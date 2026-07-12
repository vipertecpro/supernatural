<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRewatchCycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['work_id' => ['required', 'integer', 'exists:works,id'], 'viewing_order_id' => ['nullable', 'integer', 'exists:viewing_orders,id']];
    }
}
