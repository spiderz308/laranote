<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Models\Folder;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use App\Services\FolderAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class FolderController extends Controller
{
    /**
     * Display a listing of the folders the user can access.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $service = app(FolderAccessService::class);
        $accessibleIds = $service->accessibleFolderIds($user);

        $folders = Folder::whereIn('id', $accessibleIds)
            ->with(['children', 'groups', 'users'])
            ->withCount('notes')
            ->get();

        return view('folders.index', [
            'folders' => $this->buildTree($folders),
        ]);
    }

    /**
     * Show the form for creating a new folder.
     */
    public function create(Request $request): View
    {
        return view('folders.create', [
            'parentFolders' => $this->availableParents($request->user()),
            'selectedParent' => $request->integer('parent_id') > 0 ? (int) $request->integer('parent_id') : null,
        ]);
    }

    /**
     * Store a newly created folder.
     */
    public function store(StoreFolderRequest $request): RedirectResponse
    {
        $folder = $request->user()->folders()->create($request->validated());

        return redirect()->route('folders.edit', $folder)->with('status', 'folder-created');
    }

    /**
     * Display the specified folder and its notes.
     */
    public function show(Folder $folder): View
    {
        $this->authorize('view', $folder);

        $notes = $folder->notes()
            ->with(['tags'])
            ->latest()
            ->paginate(10);

        return view('folders.show', [
            'folder' => $folder->load(['children', 'parent']),
            'notes' => $notes,
        ]);
    }

    /**
     * Show the form for editing the specified folder.
     */
    public function edit(Request $request, Folder $folder): View
    {
        $this->authorize('update', $folder);

        return view('folders.edit', [
            'folder' => $folder->load(['groups', 'users']),
            'parentFolders' => $this->availableParents($request->user(), $folder),
            'groups' => Group::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified folder, including access assignments.
     */
    public function update(UpdateFolderRequest $request, Folder $folder): RedirectResponse
    {
        $this->authorize('update', $folder);

        $folder->update($request->safe()->only(['name', 'parent_id']));

        $this->syncAssignments($folder, $request);

        return redirect()->route('folders.edit', $folder)->with('status', 'folder-updated');
    }

    /**
     * Remove the specified folder.
     */
    public function destroy(Folder $folder): RedirectResponse
    {
        $this->authorize('delete', $folder);

        $folder->notes()->update(['folder_id' => null]);
        $folder->delete();

        return redirect()->route('folders.index')->with('status', 'folder-deleted');
    }

    /**
     * Replace the group and user access assignments on a folder.
     */
    private function syncAssignments(Folder $folder, UpdateFolderRequest $request): void
    {
        $groupAssignments = collect($request->validated('group_assignments', []))
            ->filter(fn (array $assignment) => isset($assignment['group_id'], $assignment['role_id']))
            ->mapWithKeys(fn (array $assignment) => [
                (int) $assignment['group_id'] => ['role_id' => (int) $assignment['role_id']],
            ])
            ->all();

        $userAssignments = collect($request->validated('user_assignments', []))
            ->filter(fn (array $assignment) => isset($assignment['user_id'], $assignment['role_id']))
            ->filter(fn (array $assignment) => (int) $assignment['user_id'] !== $folder->user_id)
            ->mapWithKeys(fn (array $assignment) => [
                (int) $assignment['user_id'] => ['role_id' => (int) $assignment['role_id']],
            ])
            ->all();

        $folder->groups()->sync($groupAssignments);
        $folder->users()->sync($userAssignments);
    }

    /**
     * Build a nested folder tree from a flat collection.
     *
     * @return Collection<int, Folder>
     */
    private function buildTree(Collection $folders): Collection
    {
        $byId = $folders->keyBy('id');

        return $folders
            ->reject(fn (Folder $folder) => $folder->parent_id !== null && $byId->has($folder->parent_id))
            ->values();
    }

    /**
     * Folders the user could nest a folder under (own or admin-accessible),
     * excluding the folder itself and any of its descendants to avoid cycles.
     *
     * @return Collection<int, Folder>
     */
    private function availableParents(User $user, ?Folder $exclude = null): Collection
    {
        $ids = app(FolderAccessService::class)->accessibleFolderIds($user);

        return Folder::whereIn('id', $ids)
            ->when($exclude, function ($query) use ($exclude) {
                $query->whereNotIn('id', app(FolderAccessService::class)->foldersUnder($exclude));
            })
            ->orderBy('name')
            ->get();
    }
}
