<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ModerationCaseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModerationCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['status' => ['required', Rule::enum(ModerationCaseStatus::class)], 'expected_version' => ['required', 'integer', 'min:0'], 'resolution_code' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/'], 'user_visible_summary' => ['nullable', 'string', 'max:2000'], 'private_internal_summary' => ['nullable', 'string', 'max:10000']];
    }
}
