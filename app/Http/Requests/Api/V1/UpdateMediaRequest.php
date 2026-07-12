<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\MediaModerationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'expected_version' => ['required', 'integer', 'min:0'], 'source_id' => ['sometimes', 'nullable', 'integer', 'exists:sources,id'],
            'content_license_id' => ['sometimes', 'nullable', 'integer', 'exists:content_licenses,id'], 'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'], 'alt_text' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'caption' => ['sometimes', 'nullable', 'string', 'max:5000'], 'attribution_text' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'copyright_owner' => ['sometimes', 'nullable', 'string', 'max:255'], 'moderation_status' => ['sometimes', Rule::enum(MediaModerationStatus::class)],
        ];
    }
}
