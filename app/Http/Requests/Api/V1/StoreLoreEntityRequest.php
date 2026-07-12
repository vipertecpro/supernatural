<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CanonClassification;
use App\Enums\LoreEntityType;
use App\Models\LoreEntity;
use App\Models\Universe;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoreEntityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', LoreEntity::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['type' => ['required', Rule::enum(LoreEntityType::class)], 'slug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('lore_entities')->where(fn ($query) => $query->where('universe_id', $this->universe()->id)->where('type', $this->input('type')))], 'canonical_name' => ['required', 'string', 'max:255'], 'short_description' => ['nullable', 'string', 'max:1000'], 'summary' => ['nullable', 'string', 'max:20000'], 'original_language' => ['required', 'string', 'max:35', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/'], 'canon_classification' => ['sometimes', Rule::enum(CanonClassification::class)], 'metadata' => ['sometimes', 'array'], 'details' => ['sometimes', 'array:category,lifecycle_status,birth_or_origin,pronouns,species_entity_id,professional_name,production_notes,location_type,parent_location_entity_id,classification,function,usage_constraints,organization_type,founded_description,event_type,occurred_on,date_precision,work_id,season_id,episode_id'], 'details.*' => ['nullable']];
    }

    private function universe(): Universe
    {
        $universe = $this->route('universe');

        return $universe instanceof Universe ? $universe : throw new \LogicException('Universe route binding is required.');
    }
}
