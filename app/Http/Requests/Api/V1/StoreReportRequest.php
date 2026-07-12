<?php

namespace App\Http\Requests\Api\V1;

use App\Domain\Moderation\Services\ReportTargetRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['category' => ['required', 'string', 'max:64', Rule::exists('report_categories', 'key')->where('is_active', true)], 'target_type' => ['required', 'string', Rule::in(ReportTargetRegistry::ALIASES)], 'target_id' => ['required', 'integer', 'min:1'], 'reason_code' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/'], 'explanation' => ['nullable', 'string', 'max:2000']];
    }
}
