<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\SpoilerSeverity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommunityPostRequest extends FormRequest
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
        return ['universe_id' => ['required', 'integer', 'exists:universes,id'], 'bunker_id' => ['nullable', 'integer', 'exists:bunkers,id'], 'reference_type' => ['nullable', Rule::in(['work', 'lore_entity'])], 'reference_id' => ['nullable', 'integer', 'required_with:reference_type'], 'title' => ['nullable', 'string', 'max:200'], 'body' => ['required', 'string', 'max:20000'], 'comments_enabled' => ['sometimes', 'boolean'], 'tags' => ['sometimes', 'array', 'max:8'], 'tags.*' => ['string', 'max:80'], 'spoiler_work_id' => ['nullable', 'integer', 'exists:works,id'], 'spoiler_season_id' => ['nullable', 'integer', 'exists:seasons,id'], 'spoiler_episode_id' => ['nullable', 'integer', 'exists:episodes,id'], 'spoiler_severity' => ['required_with:spoiler_work_id', Rule::enum(SpoilerSeverity::class)], 'spoiler_warning' => ['nullable', 'string', 'max:240']];
    }
}
