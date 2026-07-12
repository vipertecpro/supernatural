<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CanonClassification;
use App\Enums\CitationEvidenceStrength;
use App\Enums\CitationReviewStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageCitations', $this->route('revision')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'target_type' => ['required', Rule::in(['editorial_revision', 'revision_item', 'revision_block'])],
            'target_id' => ['required', 'integer', 'min:1'],
            'source_ids' => ['required', 'array', 'min:1', 'max:10'],
            'source_ids.*' => ['integer', 'distinct', 'exists:sources,id'],
            'field_key' => ['nullable', 'string', 'max:64'],
            'locator' => ['nullable', 'string', 'max:500'],
            'page_number' => ['nullable', 'string', 'max:32'],
            'timecode' => ['nullable', 'string', 'max:32'],
            'chapter' => ['nullable', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:255'],
            'quotation_excerpt' => ['nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:1000'],
            'evidence_strength' => ['required', Rule::enum(CitationEvidenceStrength::class)],
            'canon_classification' => ['required', Rule::enum(CanonClassification::class)],
            'review_status' => ['sometimes', Rule::enum(CitationReviewStatus::class)],
        ];
    }
}
