<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\BunkerVisibility;
use App\Enums\SpoilerSeverity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBunkerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:120'], 'slug' => ['nullable', 'string', 'max:140', 'alpha_dash:ascii'], 'description' => ['nullable', 'string', 'max:5000'], 'rules_summary' => ['nullable', 'string', 'max:1000'], 'visibility' => ['required', Rule::enum(BunkerVisibility::class)], 'requires_approval' => ['sometimes', 'boolean'], 'default_locale' => ['sometimes', 'string', 'max:12'], 'spoiler_severity' => ['nullable', Rule::enum(SpoilerSeverity::class)], 'category_ids' => ['sometimes', 'array', 'max:5'], 'category_ids.*' => ['integer', 'exists:bunker_categories,id']];
    }
}
