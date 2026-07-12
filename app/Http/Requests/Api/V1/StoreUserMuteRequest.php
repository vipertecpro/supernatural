<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserMuteScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserMuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['target_user_id' => ['required', 'integer', 'exists:users,id', 'different:'.(string) $this->user()?->id], 'scope' => ['required', Rule::enum(UserMuteScope::class)], 'expires_at' => ['nullable', 'date', 'after:now']];
    }
}
