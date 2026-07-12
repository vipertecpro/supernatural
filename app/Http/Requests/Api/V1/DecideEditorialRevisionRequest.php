<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DecideEditorialRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'explanation' => ['required', 'string', 'max:2000'],
            'private_note' => ['nullable', 'string', 'max:5000'],
            'findings' => ['nullable', 'array', 'max:50'],
        ];
    }
}
