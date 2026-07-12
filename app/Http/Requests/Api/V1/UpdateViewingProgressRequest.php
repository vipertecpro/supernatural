<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ProgressSource;
use App\Enums\ProgressStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateViewingProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['expected_version' => ['sometimes', 'integer', 'min:0'], 'status' => ['sometimes', Rule::enum(ProgressStatus::class)], 'source' => ['sometimes', Rule::enum(ProgressSource::class)], 'progress_basis_points' => ['sometimes', 'integer', 'between:0,10000'], 'runtime_position_seconds' => ['sometimes', 'nullable', 'integer', 'min:0'], 'journey_id' => ['sometimes', 'nullable', 'integer', 'exists:user_viewing_journeys,id'], 'rewatch_cycle_id' => ['sometimes', 'nullable', 'integer', 'exists:rewatch_cycles,id'], 'client_request_id' => ['sometimes', 'nullable', 'string', 'max:100'], 'allow_backward' => ['sometimes', 'boolean'], 'reset_spoiler_knowledge' => ['sometimes', 'boolean']];
    }
}
