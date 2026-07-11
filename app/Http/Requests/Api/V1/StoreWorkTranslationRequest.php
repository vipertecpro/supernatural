<?php

namespace App\Http\Requests\Api\V1;

use App\Models\WorkTranslation;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', WorkTranslation::class)
            && $this->user()->can('update', $this->route('work'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:35', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/'],
            'title' => ['required', 'string', 'max:255'],
            'short_title' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:10000'],
            'synopsis' => ['nullable', 'string', 'max:100000'],
            'translated_from_locale' => ['nullable', 'string', 'max:35', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/'],
        ];
    }
}
