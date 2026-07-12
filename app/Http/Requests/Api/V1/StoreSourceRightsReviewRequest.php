<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\PermissionName;
use App\Enums\RightsDecision;
use App\Enums\RightsUseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSourceRightsReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(PermissionName::EditorialRightsAssess) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'source_id' => ['required', 'integer', 'exists:sources,id'],
            'use_type' => ['required', Rule::enum(RightsUseType::class)],
            'decision' => ['required', Rule::enum(RightsDecision::class)],
            'basis' => ['required', 'string', 'max:1000'],
            'content_license_id' => ['nullable', 'integer', 'exists:content_licenses,id'],
            'rights_holder' => ['nullable', 'string', 'max:255'],
            'permission_reference' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
