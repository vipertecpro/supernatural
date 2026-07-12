<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class StoreViewingOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'viewing_order_id' => ['present', 'nullable', 'integer'],
            'expected_version' => ['required', 'integer', 'min:0'],
        ];
    }
}
