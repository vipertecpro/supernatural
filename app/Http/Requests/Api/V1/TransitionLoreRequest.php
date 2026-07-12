<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransitionLoreRequest extends FormRequest
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
        return ['expected_version' => ['required', 'integer', 'min:0'], 'is_public' => ['sometimes', 'boolean']];
    }

    public function expectedVersion(): int
    {
        return $this->integer('expected_version');
    }

    public function isPublic(): bool
    {
        return $this->boolean('is_public', true);
    }
}
