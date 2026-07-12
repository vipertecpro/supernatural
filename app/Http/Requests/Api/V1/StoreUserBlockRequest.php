<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['target_user_id' => ['required', 'integer', 'exists:users,id', 'different:'.(string) $this->user()?->id], 'reason_code' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9_\-]+$/']];
    }
}
