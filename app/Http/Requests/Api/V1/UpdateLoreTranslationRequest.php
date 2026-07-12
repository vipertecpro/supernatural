<?php

namespace App\Http\Requests\Api\V1;

use App\Models\LoreEntityTranslation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLoreTranslationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $translation = $this->route('translation');

        return $translation instanceof LoreEntityTranslation && $this->user()?->can('update', $translation->loreEntity) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['expected_version' => ['required', 'integer', 'min:0'], 'name' => ['sometimes', 'string', 'max:255'], 'short_name' => ['nullable', 'string', 'max:255'], 'short_description' => ['nullable', 'string', 'max:1000'], 'summary' => ['nullable', 'string', 'max:20000'], 'source_locale' => ['nullable', 'string', 'max:35']];
    }
}
