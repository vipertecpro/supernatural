<?php

namespace App\Http\Requests\Api\V1;

use App\Models\LoreEntity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoreTranslationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $entity = $this->route('entity');

        return $entity instanceof LoreEntity && $this->user()?->can('update', $entity) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $entity = $this->route('entity');

        return ['locale' => ['required', 'string', 'max:35', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/', Rule::unique('lore_entity_translations')->where('lore_entity_id', $entity instanceof LoreEntity ? $entity->id : 0)], 'name' => ['required', 'string', 'max:255'], 'short_name' => ['nullable', 'string', 'max:255'], 'short_description' => ['nullable', 'string', 'max:1000'], 'summary' => ['nullable', 'string', 'max:20000'], 'source_locale' => ['nullable', 'string', 'max:35']];
    }
}
