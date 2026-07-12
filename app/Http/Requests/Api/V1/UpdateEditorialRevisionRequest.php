<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEditorialRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('revision')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['summary' => ['sometimes', 'string', 'max:500'], 'metadata' => ['nullable', 'array', 'max:20']];
    }
}
