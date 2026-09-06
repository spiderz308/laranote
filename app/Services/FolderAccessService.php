<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FolderAccessService
{
    /**
     * Resolve the effective role a user holds on a folder, considering
     * explicit user assignments, group assignments and parent-folder inheritance.
     *
     * @return string|null The strongest role name, or null when the user has no access.
     */
    public function roleFor(User $user, Folder $folder): ?string
    {
        if ($folder->user_id === $user->id) {
            return Role::where('name', 'admin')->value('name');
        }

        $folderIds = array_merge([$folder->id], $this->ancestorIds($folder));
        $groupIds = $user->groups()->pluck('groups.id')->all();

        $userRoles = $user->accessibleFolders()
            ->whereIn('folders.id', $folderIds)
            ->pluck('role_id');

        $groupRoleIds = DB::table('folder_group')
            ->whereIn('folder_id', $folderIds)
            ->whereIn('group_id', $groupIds)
            ->pluck('role_id');

        $names = Role::whereIn('id', $userRoles->merge($groupRoleIds))->pluck('name');

        return $this->strongestRole($names);
    }

    /**
     * Determine whether the user can access a folder with at least the given role.
     */
    public function can(User $user, Folder $folder, string $role): bool
    {
        $priority = ['viewer' => 1, 'editor' => 2, 'admin' => 3];

        $effective = $this->roleFor($user, $folder);

        return $effective !== null && ($priority[$effective] ?? 0) >= ($priority[$role] ?? 0);
    }

    /**
     * The ids of every folder the user can access, including descendants of
     * folders they own or have been granted access to.
     *
     * @return array<int, int>
     */
    public function accessibleFolderIds(User $user): array
    {
        $owned = Folder::where('user_id', $user->id)->pluck('id');
        $direct = $user->accessibleFolders()->pluck('folders.id');
        $viaGroups = DB::table('folder_group')
            ->whereIn('group_id', $user->groups()->pluck('groups.id'))
            ->pluck('folder_id');

        return $this->rootIdsWithDescendants($owned->merge($direct)->merge($viaGroups)->unique()->all());
    }

    /**
     * The ids of the folder itself plus every descendant folder.
     *
     * @return array<int, int>
     */
    public function foldersUnder(Folder $folder): array
    {
        $all = Folder::all(['id', 'parent_id']);
        $children = $all->groupBy('parent_id');

        return array_values(array_unique([
            $folder->getKey(),
            ...$this->descendants($children, $folder->getKey()),
        ]));
    }

    /**
     * Expand a set of root folder ids with every descendant folder id.
     *
     * @param  array<int, int>  $rootIds
     * @return array<int, int>
     */
    private function rootIdsWithDescendants(array $rootIds): array
    {
        $all = Folder::all(['id', 'parent_id']);

        $children = $all->groupBy('parent_id');

        $result = [];

        foreach ($rootIds as $rootId) {
            $result[] = $rootId;
            $result = array_merge($result, $this->descendants($children, $rootId));
        }

        return array_values(array_unique($result));
    }

    /**
     * Recursively collect descendant ids for a node, tracking visited ids to
     * guarantee termination even if the tree contains a cycle.
     *
     * @param  Collection<int, Collection<int, Folder>>  $children
     * @return array<int, int>
     */
    private function descendants($children, int $parentId, array $visited = []): array
    {
        if (in_array($parentId, $visited, true)) {
            return [];
        }

        $visited[] = $parentId;

        $result = [];

        foreach ($children->get($parentId, collect()) as $child) {
            if (in_array($child->getKey(), $visited, true)) {
                continue;
            }

            $result[] = $child->getKey();
            $result = array_merge($result, $this->descendants($children, $child->getKey(), $visited));
        }

        return $result;
    }

    /**
     * The ids of every ancestor folder of the given folder, stopping early to
     * guarantee termination even if the tree contains a cycle.
     *
     * @return array<int, int>
     */
    private function ancestorIds(Folder $folder): array
    {
        $ids = [];
        $seen = [];
        $current = $folder->parent;

        while ($current !== null && ! in_array($current->getKey(), $seen, true)) {
            $seen[] = $current->getKey();
            $ids[] = $current->getKey();
            $current = $current->parent;
        }

        return $ids;
    }

    /**
     * Return the strongest of a list of role names, or null when empty.
     *
     * @param  iterable<int, string>  $names
     */
    private function strongestRole(iterable $names): ?string
    {
        $priority = ['viewer' => 1, 'editor' => 2, 'admin' => 3];

        $best = null;
        $bestScore = 0;

        foreach ($names as $name) {
            $score = $priority[$name] ?? 0;
            if ($score > $bestScore) {
                $best = $name;
                $bestScore = $score;
            }
        }

        return $best;
    }
}
