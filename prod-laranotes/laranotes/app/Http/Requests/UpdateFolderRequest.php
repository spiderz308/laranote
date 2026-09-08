<?php

namespace App\Http\Requests;

use App\Rules\ValidParentFolder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFolderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:folders,id', new ValidParentFolder(forUpdate: true, folder: $this->route('folder'))],
            'group_assignments' => ['nullable', 'array'],
            'group_assignments.*.group_id' => ['required', 'integer', 'exists:groups,id'],
            'group_assignments.*.role_id' => ['required', 'integer', 'exists:roles,id'],
            'user_assignments' => ['nullable', 'array'],
            'user_assignments.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'user_assignments.*.role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }
}
