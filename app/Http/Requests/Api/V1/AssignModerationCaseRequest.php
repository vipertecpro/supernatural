<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AssignModerationCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['moderator_user_id' => ['required', 'integer', 'exists:users,id'], 'private_note' => ['nullable', 'string', 'max:1000']];
    }
}
