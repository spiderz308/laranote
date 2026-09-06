<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;
use App\Services\FolderAccessService;

class FolderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the folder.
     */
    public function view(User $user, Folder $folder): bool
    {
        return $folder->user_id === $user->id
            || app(FolderAccessService::class)->can($user, $folder, 'viewer');
    }

    /**
     * Determine whether the user can create folders.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can rename or manage access to the folder.
     */
    public function update(User $user, Folder $folder): bool
    {
        return $folder->user_id === $user->id
            || app(FolderAccessService::class)->can($user, $folder, 'admin');
    }

    /**
     * Determine whether the user can delete the folder.
     */
    public function delete(User $user, Folder $folder): bool
    {
        return $folder->user_id === $user->id
            || app(FolderAccessService::class)->can($user, $folder, 'admin');
    }

    /**
     * Determine whether the user can restore the folder.
     */
    public function restore(User $user, Folder $folder): bool
    {
        return $folder->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the folder.
     */
    public function forceDelete(User $user, Folder $folder): bool
    {
        return $folder->user_id === $user->id;
    }
}
