<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\AppearanceKind;
use App\Enums\AppearanceSignificance;
use App\Enums\CanonClassification;
use App\Models\EntityAppearance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEntityAppearanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->appearance()) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['expected_version' => ['required', 'integer', 'min:0'], 'work_id' => ['sometimes', 'integer', 'exists:works,id'], 'season_id' => ['nullable', 'integer', 'exists:seasons,id'], 'episode_id' => ['nullable', 'integer', 'exists:episodes,id'], 'kind' => ['sometimes', Rule::enum(AppearanceKind::class)], 'significance' => ['sometimes', Rule::enum(AppearanceSignificance::class)], 'is_credited' => ['nullable', 'boolean'], 'position' => ['sometimes', 'integer', 'min:0'], 'canon_classification' => ['sometimes', Rule::enum(CanonClassification::class)], 'notes' => ['nullable', 'string', 'max:5000']];
    }

    private function appearance(): EntityAppearance
    {
        $appearance = $this->route('appearance');

        return $appearance instanceof EntityAppearance ? $appearance : throw new \LogicException('Appearance route binding is required.');
    }
}
