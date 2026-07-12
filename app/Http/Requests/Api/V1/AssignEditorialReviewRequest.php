<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AssignEditorialReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assign', $this->route('revision')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reviewer_user_id' => ['required', 'integer', 'exists:users,id'],
            'due_at' => ['nullable', 'date', 'after_or_equal:today'],
            'internal_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
