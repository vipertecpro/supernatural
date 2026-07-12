<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ProgressStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateViewingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['expected_version' => ['required', 'integer', 'min:0'], 'position_seconds' => ['sometimes', 'integer', 'min:0'], 'update_progress' => ['sometimes', 'boolean'], 'progress_status' => ['sometimes', Rule::enum(ProgressStatus::class)], 'client_request_id' => ['sometimes', 'nullable', 'string', 'max:100']];
    }
}
