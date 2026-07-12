<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class StoreUniverseInterestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'universe_ids' => ['sometimes', 'array', 'max:25'],
            'universe_ids.*' => ['integer', 'distinct'],
            'expected_version' => ['required', 'integer', 'min:0'],
        ];
    }

    /** @return list<int> */
    public function universeIds(): array
    {
        return array_values(array_map('intval', $this->validated('universe_ids', [])));
    }
}
