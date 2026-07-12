<?php

namespace App\Http\Requests\Api\V1;

use App\Domain\Moderation\Services\ReportTargetRegistry;
use App\Enums\ContentRestrictionType;
use App\Enums\ModerationActionType;
use App\Enums\RestrictionScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyModerationActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['type' => ['required', Rule::enum(ModerationActionType::class)], 'target_user_id' => ['nullable', 'integer', 'exists:users,id'], 'target_type' => ['nullable', Rule::in(ReportTargetRegistry::ALIASES)], 'target_id' => ['nullable', 'integer', 'min:1'], 'reason_code' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/'], 'user_visible_explanation' => ['required', 'string', 'max:2000'], 'private_moderator_note' => ['nullable', 'string', 'max:10000'], 'expires_at' => ['nullable', 'date', 'after:now'], 'restriction_scopes' => ['sometimes', 'array', 'max:7'], 'restriction_scopes.*' => ['distinct', Rule::enum(RestrictionScope::class)], 'content_restriction_type' => ['nullable', Rule::enum(ContentRestrictionType::class)], 'supersedes_action_id' => ['nullable', 'integer', 'exists:moderation_actions,id']];
    }
}
