<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CanonClassification;
use App\Enums\DatePrecision;
use App\Enums\RelationshipConfidence;
use App\Enums\TimelineEntryType;
use App\Models\TimelineEntry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTimelineEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', TimelineEntry::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['type' => ['required', Rule::enum(TimelineEntryType::class)], 'work_id' => ['nullable', 'integer', 'exists:works,id'], 'season_id' => ['nullable', 'integer', 'exists:seasons,id'], 'episode_id' => ['nullable', 'integer', 'exists:episodes,id'], 'lore_event_entity_id' => ['nullable', 'integer', 'exists:lore_entities,id'], 'lore_relationship_id' => ['nullable', 'integer', 'exists:lore_relationships,id'], 'title' => ['required', 'string', 'max:255'], 'summary' => ['nullable', 'string', 'max:10000'], 'sort_key' => ['required', 'numeric', 'between:-999999999999,999999999999'], 'sequence_number' => ['nullable', 'integer', 'min:0'], 'in_universe_date' => ['nullable', 'date_format:Y-m-d'], 'date_precision' => ['nullable', Rule::enum(DatePrecision::class)], 'relative_order' => ['nullable', 'string', 'max:255'], 'canon_classification' => ['sometimes', Rule::enum(CanonClassification::class)], 'confidence' => ['sometimes', Rule::enum(RelationshipConfidence::class)], 'entity_ids' => ['sometimes', 'array', 'max:50'], 'entity_ids.*' => ['integer', 'distinct', 'exists:lore_entities,id']];
    }
}
