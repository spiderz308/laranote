<?php

namespace App\Rules;

use App\Models\Folder;
use App\Services\FolderAccessService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNoteFolder implements ValidationRule
{
    /**
     * Ensure the folder a note is filed into is one the user can write notes to.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $folder = Folder::find((int) $value);

        if ($folder === null) {
            return;
        }

        $user = request()->user();

        if ($user === null) {
            return;
        }

        if (! app(FolderAccessService::class)->can($user, $folder, 'editor')) {
            $fail('You can only file notes into folders you can edit.');
        }
    }
}
