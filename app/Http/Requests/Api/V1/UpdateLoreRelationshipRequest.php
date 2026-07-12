<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CanonClassification;
use App\Enums\DatePrecision;
use App\Enums\RelationshipConfidence;
use App\Models\LoreRelationship;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLoreRelationshipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->relationship()) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['expected_version' => ['required', 'integer', 'min:0'], 'canon_classification' => ['sometimes', Rule::enum(CanonClassification::class)], 'confidence' => ['sometimes', Rule::enum(RelationshipConfidence::class)], 'start_work_id' => ['nullable', 'integer', 'exists:works,id'], 'start_season_id' => ['nullable', 'integer', 'exists:seasons,id'], 'start_episode_id' => ['nullable', 'integer', 'exists:episodes,id'], 'end_work_id' => ['nullable', 'integer', 'exists:works,id'], 'end_season_id' => ['nullable', 'integer', 'exists:seasons,id'], 'end_episode_id' => ['nullable', 'integer', 'exists:episodes,id'], 'starts_on' => ['nullable', 'date_format:Y-m-d'], 'ends_on' => ['nullable', 'date_format:Y-m-d'], 'date_precision' => ['nullable', Rule::enum(DatePrecision::class)], 'qualifier' => ['nullable', 'string', 'max:1000'], 'editorial_note' => ['nullable', 'string', 'max:5000']];
    }

    private function relationship(): LoreRelationship
    {
        $relationship = $this->route('relationship');

        return $relationship instanceof LoreRelationship ? $relationship : throw new \LogicException('Lore relationship route binding is required.');
    }
}
