<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\SyncCollaboratorsRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Folder;
use App\Models\Note;
use App\Models\NoteVersion;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Services\FolderAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class NoteController extends Controller
{
    /**
     * Display a listing of visible notes with optional search, tag, folder, and sort.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $tagId = $request->integer('tag');
        $folderId = $request->integer('folder');
        $sort = $request->string('sort')->toString() ?: 'updated_desc';

        $user = $request->user();
        $accessibleFolderIds = app(FolderAccessService::class)->accessibleFolderIds($user);

        $notes = Note::query()
            ->with(['tags', 'folder', 'owner'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            }))
            ->when($tagId > 0, fn ($query) => $query->whereHas('tags', fn ($query) => $query->whereKey($tagId)))
            ->when($folderId > 0, fn ($query) => $query->where('folder_id', $folderId))
            ->where(function ($query) use ($user, $accessibleFolderIds) {
                $query->where('user_id', $user->id)
                    ->when($accessibleFolderIds !== [], fn ($query) => $query->orWhereIn('folder_id', $accessibleFolderIds))
                    ->orWhereHas('collaborators', fn ($query) => $query->whereKey($user->id));
            })
            ->when($sort === 'updated_asc', fn ($query) => $query->oldest('updated_at'))
            ->when($sort === 'title_asc', fn ($query) => $query->orderBy('title'))
            ->when($sort === 'title_desc', fn ($query) => $query->orderByDesc('title'))
            ->when(in_array($sort, ['', 'updated_desc'], true), fn ($query) => $query->latest('updated_at'))
            ->paginate(10);

        return view('notes.index', [
            'notes' => $notes,
            'tags' => Tag::orderBy('name')->get(),
            'folders' => $this->availableFolders($user),
            'selectedTag' => $tagId > 0 ? Tag::find($tagId) : null,
            'selectedFolder' => $folderId > 0 ? Folder::find($folderId) : null,
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form for creating a new note.
     */
    public function create(Request $request): View
    {
        return view('notes.create', [
            'folders' => $this->availableFolders($request->user()),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created note.
     */
    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $data = $request->safe()->merge([
            'is_public' => $request->boolean('is_public'),
        ])->except('tags');

        $note = $request->user()->notes()->create($data);

        $this->syncTags($note, $request->input('tags'));

        $note->versions()->create([
            'user_id' => $request->user()->id,
            'title' => $note->title,
            'content' => $note->content,
        ]);

        return redirect()->route('notes.index')->with('status', 'note-created');
    }

    /**
     * Display the specified note.
     */
    public function show(Request $request, Note $note): View
    {
        if ($request->user() === null) {
            abort_unless($note->is_public, 404);

            return view('notes.public', ['note' => $note->load(['tags', 'folder'])]);
        }

        $this->authorize('view', $note);

        return view('notes.show', ['note' => $note->load(['tags', 'folder', 'versions'])]);
    }

    /**
     * Show the form for editing the specified note.
     */
    public function edit(Request $request, Note $note): View
    {
        $this->authorize('update', $note);

        return view('notes.edit', [
            'note' => $note,
            'folders' => $this->availableFolders($request->user()),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified note.
     */
    public function update(UpdateNoteRequest $request, Note $note): RedirectResponse
    {
        $this->authorize('update', $note);

        $note->update($request->safe()->merge([
            'is_public' => $request->boolean('is_public'),
        ])->except('tags'));

        $this->syncTags($note, $request->input('tags'));

        $note->versions()->create([
            'user_id' => $request->user()->id,
            'title' => $note->title,
            'content' => $note->content,
        ]);

        return redirect()->route('notes.show', $note)->with('status', 'note-updated');
    }

    /**
     * Display the current user's trashed (soft-deleted) notes.
     */
    public function trash(Request $request): View
    {
        $trashed = Note::query()
            ->with(['tags', 'folder'])
            ->where('user_id', $request->user()->id)
            ->onlyTrashed()
            ->latest()
            ->paginate(10);

        return view('notes.trash', ['notes' => $trashed]);
    }

    /**
     * Restore a soft-deleted note.
     */
    public function restore(Note $note): RedirectResponse
    {
        $this->authorize('restore', $note);

        $note->restore();

        return redirect()->route('notes.trash')->with('status', 'note-restored');
    }

    /**
     * Permanently delete a soft-deleted note.
     */
    public function forceDelete(Note $note): RedirectResponse
    {
        $this->authorize('forceDelete', $note);

        $note->forceDelete();

        return redirect()->route('notes.trash')->with('status', 'note-purged');
    }

    /**
     * Remove the specified note.
     */
    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return redirect()->route('notes.index')->with('status', 'note-deleted');
    }

    /**
     * Show the manage-collaborators page for a note.
     */
    public function share(Note $note): View
    {
        $this->authorize('manageCollaborators', $note);

        return view('notes.share', [
            'note' => $note->load(['collaborators']),
            'users' => User::query()
                ->select(['id', 'name', 'email'])
                ->orderBy('name')
                ->get(),
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    /**
     * Sync the collaborators of a note.
     */
    public function syncCollaborators(SyncCollaboratorsRequest $request, Note $note): RedirectResponse
    {
        $this->authorize('manageCollaborators', $note);

        $collaborators = collect($request->validated('collaborators', []))
            ->filter(fn (array $entry) => isset($entry['user_id'], $entry['role_id']))
            ->filter(fn (array $entry) => (int) $entry['user_id'] !== $note->user_id)
            ->mapWithKeys(fn (array $entry) => [
                (int) $entry['user_id'] => ['role_id' => (int) $entry['role_id']],
            ])
            ->all();

        $note->collaborators()->sync($collaborators);

        return redirect()->route('notes.show', $note)->with('status', 'collaborators-synced');
    }

    /**
     * Display a single version of the note.
     */
    public function showVersion(Note $note, NoteVersion $version): View
    {
        abort_unless($version->note_id === $note->id, 404);

        $this->authorize('view', $note);

        return view('notes.version', [
            'note' => $note,
            'version' => $version,
        ]);
    }

    /**
     * Restore the note to a previous version (creates a new version entry).
     */
    public function restoreVersion(Request $request, Note $note, NoteVersion $version): RedirectResponse
    {
        abort_unless($version->note_id === $note->id, 404);

        $this->authorize('update', $note);

        $note->update([
            'title' => $version->title,
            'content' => $version->content,
        ]);

        $note->versions()->create([
            'user_id' => $request->user()->id,
            'title' => $note->title,
            'content' => $note->content,
        ]);

        return redirect()->route('notes.show', $note)->with('status', 'version-restored');
    }

    /**
     * The folders the user can file notes into (own folders plus granted ones).
     *
     * @return Collection<int, Folder>
     */
    private function availableFolders(User $user): Collection
    {
        $ids = app(FolderAccessService::class)->accessibleFolderIds($user);

        return Folder::whereIn('id', $ids)->with('children')->orderBy('name')->get();
    }

    /**
     * Sync note tags, creating new tags on the fly. Accepts either an array of
     * names or a single comma-separated string.
     *
     * @param  array<int, string>|string|null  $input
     */
    private function syncTags(Note $note, array|string|null $input): void
    {
        $names = is_array($input)
            ? $input
            : explode(',', (string) $input);

        $tagIds = collect($names)
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique()
            ->map(fn (string $name) => Tag::firstOrCreate(['name' => $name])->id)
            ->values()
            ->all();

        $note->tags()->sync($tagIds);
    }
}
