<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreViewingProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in(['skip', 'not_started', 'watched_through', 'completed_work'])],
            'work_id' => [Rule::requiredIf(fn (): bool => $this->string('mode')->toString() !== 'skip'), 'nullable', 'integer'],
            'episode_id' => [Rule::requiredIf(fn (): bool => $this->string('mode')->toString() === 'watched_through'), 'nullable', 'integer'],
            'expected_version' => ['required', 'integer', 'min:0'],
        ];
    }
}
