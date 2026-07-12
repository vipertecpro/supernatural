<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CanonClassification;
use App\Enums\LoreEntityType;
use App\Models\LoreEntity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLoreEntityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->entity()) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $entity = $this->entity();

        return ['expected_version' => ['required', 'integer', 'min:0'], 'type' => ['sometimes', Rule::enum(LoreEntityType::class)], 'slug' => ['sometimes', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('lore_entities')->where(fn ($query) => $query->where('universe_id', $entity->universe_id)->where('type', $this->input('type', $entity->type->value)))->ignore($entity)], 'canonical_name' => ['sometimes', 'string', 'max:255'], 'short_description' => ['nullable', 'string', 'max:1000'], 'summary' => ['nullable', 'string', 'max:20000'], 'original_language' => ['sometimes', 'string', 'max:35'], 'canon_classification' => ['sometimes', Rule::enum(CanonClassification::class)], 'metadata' => ['sometimes', 'array'], 'details' => ['sometimes', 'array:category,lifecycle_status,birth_or_origin,pronouns,species_entity_id,professional_name,production_notes,location_type,parent_location_entity_id,classification,function,usage_constraints,organization_type,founded_description,event_type,occurred_on,date_precision,work_id,season_id,episode_id'], 'details.*' => ['nullable']];
    }

    private function entity(): LoreEntity
    {
        $entity = $this->route('entity');

        return $entity instanceof LoreEntity ? $entity : throw new \LogicException('Lore entity route binding is required.');
    }
}
