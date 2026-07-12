<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CanonClassification;
use App\Enums\TimelineType;
use App\Models\Timeline;
use App\Models\Universe;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTimelineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Timeline::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['lore_entity_id' => ['nullable', 'integer', 'exists:lore_entities,id'], 'work_id' => ['nullable', 'integer', 'exists:works,id'], 'name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('timelines')->where('universe_id', $this->universe()->id)], 'type' => ['required', Rule::enum(TimelineType::class)], 'description' => ['nullable', 'string', 'max:10000'], 'canon_classification' => ['sometimes', Rule::enum(CanonClassification::class)]];
    }

    private function universe(): Universe
    {
        $universe = $this->route('universe');

        return $universe instanceof Universe ? $universe : throw new \LogicException('Universe route binding is required.');
    }
}
