<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\BunkerRuleCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBunkerRuleRequest extends FormRequest
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
        return ['title' => ['required', 'string', 'max:120'], 'description' => ['required', 'string', 'max:1000'], 'category' => ['required', Rule::enum(BunkerRuleCategory::class)], 'position' => ['required', 'integer', 'min:0', 'max:100']];
    }
}
