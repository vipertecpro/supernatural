<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\LoreAliasType;
use App\Models\LoreAlias;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLoreAliasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->alias()) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['expected_version' => ['required', 'integer', 'min:0'], 'name' => ['sometimes', 'string', 'max:255'], 'type' => ['sometimes', Rule::enum(LoreAliasType::class)], 'locale' => ['nullable', 'string', 'max:35'], 'spoiler_sensitive' => ['sometimes', 'boolean']];
    }

    private function alias(): LoreAlias
    {
        $alias = $this->route('alias');

        return $alias instanceof LoreAlias ? $alias : throw new \LogicException('Lore alias route binding is required.');
    }
}
