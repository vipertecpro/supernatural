<?php

namespace App\Http\Requests\Api\V1;

use App\Domain\Moderation\Services\ReportTargetRegistry;
use App\Enums\ReportPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModerationCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['target_type' => ['required', Rule::in(ReportTargetRegistry::ALIASES)], 'target_id' => ['required', 'integer', 'min:1'], 'subject_user_id' => ['nullable', 'integer', 'exists:users,id'], 'priority' => ['required', Rule::enum(ReportPriority::class)], 'report_ids' => ['sometimes', 'array', 'max:100'], 'report_ids.*' => ['integer', 'distinct', 'exists:reports,id']];
    }
}
