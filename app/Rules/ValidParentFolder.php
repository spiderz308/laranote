<?php

namespace App\Rules;

use App\Models\Folder;
use App\Services\FolderAccessService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidParentFolder implements ValidationRule
{
    public function __construct(
        private readonly bool $forUpdate,
        private readonly ?Folder $folder = null,
    ) {}

    /**
     * Validate the parent folder is accessible to the user and would not
     * introduce a folder cycle.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $parent = Folder::find((int) $value);

        if ($parent === null) {
            return;
        }

        $user = request()->user();

        if ($user === null) {
            return;
        }

        if ($this->forUpdate) {
            $cycleIds = app(FolderAccessService::class)->foldersUnder($this->folder);

            if (in_array($parent->getKey(), $cycleIds, true)) {
                $fail('A folder cannot be nested inside itself or one of its subfolders.');
            }
        }

        if (! app(FolderAccessService::class)->can($user, $parent, 'viewer')) {
            $fail('The selected parent folder is not accessible to you.');
        }
    }
}
