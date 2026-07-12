<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPreferenceState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['preferences' => ['required', 'array', 'max:50'], 'preferences.*.type' => ['required', 'string', 'max:96'], 'preferences.*.channel' => ['required', Rule::enum(NotificationChannel::class)], 'preferences.*.state' => ['required', Rule::enum(NotificationPreferenceState::class)], 'preferences.*.expected_version' => ['required', 'integer', 'min:0']];
    }

    /** @return list<array{type:string, channel:string, state:string, expected_version:int}> */
    public function preferences(): array
    {
        $preferences = $this->validated('preferences');
        if (! is_array($preferences)) {
            return [];
        }

        return array_values($preferences);
    }
}
