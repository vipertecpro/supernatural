<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class StorePrivacyDefaultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'confirm_private_defaults' => ['accepted'],
            'expected_version' => ['required', 'integer', 'min:0'],
        ];
    }
}
