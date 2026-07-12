<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['moderation_action_id' => ['required', 'integer', 'exists:moderation_actions,id'], 'explanation' => ['required', 'string', 'min:10', 'max:3000']];
    }
}
