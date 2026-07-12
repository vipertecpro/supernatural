<?php

namespace App\Http\Requests\Onboarding;

use App\Enums\SpoilerTolerance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpoilerPreferencesRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['show_warnings' => $this->boolean('show_warnings')]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tolerance' => ['required', Rule::enum(SpoilerTolerance::class)],
            'show_warnings' => ['required', 'boolean'],
            'rewatch_behavior' => ['required', Rule::in(['historical', 'current_cycle'])],
            'expected_version' => ['required', 'integer', 'min:0'],
        ];
    }
}
