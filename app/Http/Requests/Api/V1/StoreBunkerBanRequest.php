<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBunkerBanRequest extends FormRequest
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
        return ['user_id' => ['required', 'integer', 'exists:users,id'], 'reason_code' => ['required', 'string', 'max:64', 'alpha_dash:ascii'], 'user_visible_explanation' => ['required', 'string', 'max:500'], 'private_note' => ['nullable', 'string', 'max:2000'], 'expires_at' => ['nullable', 'date', 'after:now']];
    }
}
