<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRevisionBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('revision')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'field' => ['required', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:35'],
            'text' => ['required', 'string', 'max:20000'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
