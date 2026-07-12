<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\AppealDecisionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['type' => ['required', Rule::enum(AppealDecisionType::class)], 'user_visible_explanation' => ['required', 'string', 'max:2000'], 'private_reviewer_note' => ['nullable', 'string', 'max:10000'], 'replacement_action_id' => ['nullable', 'integer', 'exists:moderation_actions,id']];
    }
}
