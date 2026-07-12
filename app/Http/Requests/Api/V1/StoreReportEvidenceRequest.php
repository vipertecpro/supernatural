<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ReportEvidenceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['type' => ['required', Rule::enum(ReportEvidenceType::class)], 'description' => ['nullable', 'string', 'max:2000', 'not_regex:/<(script|iframe)\b/i'], 'media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'], 'source_id' => ['nullable', 'integer', 'exists:sources,id'], 'citation_id' => ['nullable', 'integer', 'exists:citations,id'], 'external_url' => ['nullable', 'url:https', 'max:2048', 'not_regex:/[\r\n]/'], 'snapshot' => ['nullable', 'string', 'max:1000']];
    }
}
