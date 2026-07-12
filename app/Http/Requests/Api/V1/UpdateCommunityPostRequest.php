<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\SpoilerSeverity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommunityPostRequest extends FormRequest
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
        return ['lock_version' => ['required', 'integer', 'min:0'], 'title' => ['sometimes', 'nullable', 'string', 'max:200'], 'body' => ['sometimes', 'string', 'max:20000'], 'comments_enabled' => ['sometimes', 'boolean'], 'spoiler_work_id' => ['nullable', 'integer', 'exists:works,id'], 'spoiler_season_id' => ['nullable', 'integer', 'exists:seasons,id'], 'spoiler_episode_id' => ['nullable', 'integer', 'exists:episodes,id'], 'spoiler_severity' => ['required_with:spoiler_work_id', Rule::enum(SpoilerSeverity::class)], 'spoiler_warning' => ['nullable', 'string', 'max:240']];
    }
}
