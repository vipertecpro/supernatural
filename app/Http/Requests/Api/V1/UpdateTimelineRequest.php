<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CanonClassification;
use App\Enums\TimelineType;
use App\Models\Timeline;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTimelineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->timeline()) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $timeline = $this->timeline();

        return ['expected_version' => ['required', 'integer', 'min:0'], 'lore_entity_id' => ['nullable', 'integer', 'exists:lore_entities,id'], 'work_id' => ['nullable', 'integer', 'exists:works,id'], 'name' => ['sometimes', 'string', 'max:255'], 'slug' => ['sometimes', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('timelines')->where('universe_id', $timeline->universe_id)->ignore($timeline)], 'type' => ['sometimes', Rule::enum(TimelineType::class)], 'description' => ['nullable', 'string', 'max:10000'], 'canon_classification' => ['sometimes', Rule::enum(CanonClassification::class)]];
    }

    private function timeline(): Timeline
    {
        $timeline = $this->route('timeline');

        return $timeline instanceof Timeline ? $timeline : throw new \LogicException('Timeline route binding is required.');
    }
}
