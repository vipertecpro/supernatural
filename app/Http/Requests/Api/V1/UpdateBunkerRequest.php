<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\BunkerVisibility;
use App\Enums\SpoilerSeverity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBunkerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['lock_version' => ['required', 'integer', 'min:0'], 'membership_id' => ['sometimes', 'integer', 'exists:bunker_memberships,id'], 'role' => ['sometimes', Rule::in(['administrator', 'moderator', 'member'])], 'name' => ['sometimes', 'string', 'max:120'], 'title' => ['sometimes', 'string', 'max:120'], 'description' => ['sometimes', 'nullable', 'string', 'max:5000'], 'rules_summary' => ['sometimes', 'nullable', 'string', 'max:1000'], 'visibility' => ['sometimes', Rule::enum(BunkerVisibility::class)], 'requires_approval' => ['sometimes', 'boolean'], 'is_active' => ['sometimes', 'boolean'], 'category' => ['sometimes', 'string', 'max:32'], 'default_locale' => ['sometimes', 'string', 'max:12'], 'spoiler_severity' => ['sometimes', 'nullable', Rule::enum(SpoilerSeverity::class)], 'category_ids' => ['sometimes', 'array', 'max:5'], 'category_ids.*' => ['integer', 'exists:bunker_categories,id'], 'rule_ids' => ['sometimes', 'array', 'max:100'], 'rule_ids.*' => ['integer', 'distinct', 'exists:bunker_rules,id']];
    }
}
