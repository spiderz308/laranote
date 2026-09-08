<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\Role;
use App\Models\User;
use App\Services\FolderAccessService;

class NotePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Note $note): bool
    {
        return $this->canAccess($user, $note, 'viewer');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note): bool
    {
        if ($note->user_id === $user->id) {
            return true;
        }

        if ($this->isCollaboratorWithRole($user, $note, ['admin', 'editor'])) {
            return true;
        }

        return $note->folder_id !== null
            && app(FolderAccessService::class)->can($user, $note->folder, 'editor');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Note $note): bool
    {
        if ($note->user_id === $user->id) {
            return true;
        }

        if ($this->isCollaboratorWithRole($user, $note, ['admin'])) {
            return true;
        }

        return $note->folder_id !== null
            && app(FolderAccessService::class)->can($user, $note->folder, 'admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Note $note): bool
    {
        return $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Note $note): bool
    {
        return $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can manage collaborators of the model.
     */
    public function manageCollaborators(User $user, Note $note): bool
    {
        if ($note->user_id === $user->id) {
            return true;
        }

        return $this->isCollaboratorWithRole($user, $note, ['admin']);
    }

    /**
     * Whether the user can view the note (owner, public, collaborator, or folder access).
     */
    private function canAccess(User $user, Note $note, string $minimumRole): bool
    {
        if ($note->user_id === $user->id) {
            return true;
        }

        if ($note->is_public) {
            return true;
        }

        if ($this->isCollaboratorWithRole($user, $note, ['admin', 'editor', 'viewer'])) {
            return true;
        }

        return $note->folder_id !== null
            && app(FolderAccessService::class)->can($user, $note->folder, $minimumRole);
    }

    /**
     * Whether the user collaborates on the note with one of the given roles.
     *
     * @param  array<int, string>  $roles
     */
    private function isCollaboratorWithRole(User $user, Note $note, array $roles): bool
    {
        $roleIds = Role::whereIn('name', $roles)->pluck('id');

        return $note->collaborators()
            ->wherePivotIn('role_id', $roleIds)
            ->whereKey($user->id)
            ->exists();
    }
}
