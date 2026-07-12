<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\SpoilerTolerance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateJourneyPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['universe_id' => ['required', 'integer', 'exists:universes,id'], 'preferred_viewing_order_id' => ['sometimes', 'nullable', 'integer', 'exists:viewing_orders,id'], 'default_locale' => ['sometimes', 'string', 'max:35'], 'auto_complete_progress' => ['sometimes', 'boolean'], 'auto_remove_completed_watchlist_items' => ['sometimes', 'boolean'], 'tolerance' => ['sometimes', Rule::enum(SpoilerTolerance::class)], 'show_warnings' => ['sometimes', 'boolean'], 'rewatch_behavior' => ['sometimes', Rule::in(['historical', 'current_cycle'])], 'expected_version' => ['sometimes', 'integer', 'min:0']];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $unknown = array_diff(array_keys($this->all()), array_keys($this->rules()));
            if ($unknown !== []) {
                $validator->errors()->add('preferences', 'Unknown preference keys are not allowed.');
            }
        }];
    }
}
