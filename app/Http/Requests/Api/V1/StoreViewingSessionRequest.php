<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ProgressSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreViewingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['target_type' => ['required', Rule::in(['work', 'season', 'episode'])], 'target_id' => ['required', 'integer', 'min:1'], 'client_session_id' => ['required', 'string', 'max:100'], 'journey_id' => ['nullable', 'integer', 'exists:user_viewing_journeys,id'], 'rewatch_cycle_id' => ['nullable', 'integer', 'exists:rewatch_cycles,id'], 'source' => ['sometimes', Rule::enum(ProgressSource::class)], 'position_seconds' => ['sometimes', 'integer', 'min:0'], 'safe_metadata' => ['sometimes', 'array', 'max:2'], 'safe_metadata.client_platform' => ['sometimes', 'string', 'max:32'], 'safe_metadata.app_version' => ['sometimes', 'string', 'max:32']];
    }
}
