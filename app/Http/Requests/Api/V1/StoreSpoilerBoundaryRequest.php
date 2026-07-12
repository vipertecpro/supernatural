<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\PermissionName;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpoilerBoundaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(PermissionName::EditorialSpoilersClassify) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'target_type' => ['required', Rule::in(['work', 'work_translation', 'season', 'episode', 'revision_block'])],
            'target_id' => ['required', 'integer', 'min:1'],
            'constraint_id' => ['nullable', 'integer', 'exists:spoiler_constraints,id'],
            'work_id' => ['required', 'integer', 'exists:works,id'],
            'season_id' => ['nullable', 'integer', 'exists:seasons,id'],
            'episode_id' => ['nullable', 'integer', 'exists:episodes,id'],
            'severity' => ['required', Rule::enum(SpoilerSeverity::class)],
            'classification_status' => ['required', Rule::enum(SpoilerClassificationStatus::class)],
            'warning' => ['nullable', 'string', 'max:500'],
        ];
    }
}
