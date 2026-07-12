<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreViewingJourneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['viewing_order_id' => ['required', 'integer', 'exists:viewing_orders,id'], 'rewatch_cycle_id' => ['nullable', 'integer', 'exists:rewatch_cycles,id']];
    }
}
