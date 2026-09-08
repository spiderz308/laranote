<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncCollaboratorsRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'collaborators' => ['nullable', 'array'],
            'collaborators.*.user_id' => ['required', 'integer', 'exists:users,id', 'distinct'],
            'collaborators.*.role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }
}
