<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Note;
use App\Services\FolderAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the user's dashboard overview.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $accessibleFolderIds = app(FolderAccessService::class)->accessibleFolderIds($user);

        $ownNotes = Note::query()->where('user_id', $user->id);
        $visibleNotes = Note::query()->where(function ($query) use ($user, $accessibleFolderIds) {
            $query->where('user_id', $user->id)
                ->when($accessibleFolderIds !== [], fn ($query) => $query->orWhereIn('folder_id', $accessibleFolderIds))
                ->orWhereHas('collaborators', fn ($query) => $query->whereKey($user->id));
        });

        $sharedWithMe = Note::query()
            ->where('user_id', '!=', $user->id)
            ->whereHas('collaborators', fn ($query) => $query->whereKey($user->id))
            ->count();

        $trashedCount = Note::query()
            ->onlyTrashed()
            ->where('user_id', $user->id)
            ->count();

        $folderCount = $accessibleFolderIds !== []
            ? Folder::whereIn('id', $accessibleFolderIds)->count()
            : 0;

        $recentNotes = $visibleNotes
            ->with(['folder', 'tags', 'owner'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $topTags = Note::query()
            ->where('user_id', $user->id)
            ->join('note_tag', 'note_tag.note_id', '=', 'notes.id')
            ->join('tags', 'tags.id', '=', 'note_tag.tag_id')
            ->select('tags.name', DB::raw('count(*) as total'))
            ->groupBy('tags.id', 'tags.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'ownNoteCount' => $ownNotes->count(),
            'sharedWithMeCount' => $sharedWithMe,
            'folderCount' => $folderCount,
            'trashedCount' => $trashedCount,
            'recentNotes' => $recentNotes,
            'topTags' => $topTags,
        ]);
    }
}
