<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\LoreAliasType;
use App\Models\LoreAlias;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoreAliasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', LoreAlias::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'type' => ['required', Rule::enum(LoreAliasType::class)], 'locale' => ['nullable', 'string', 'max:35'], 'spoiler_sensitive' => ['sometimes', 'boolean']];
    }
}
