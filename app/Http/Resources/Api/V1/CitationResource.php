<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Citation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Citation */
class CitationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'target' => ['type' => $this->citable_type, 'id' => $this->citable_id],
            'source_ids' => $this->whenLoaded('citationSources', fn () => $this->citationSources->pluck('source_id')),
            'field_key' => $this->field_key,
            'locator' => $this->locator,
            'page_number' => $this->page_number,
            'timecode' => $this->timecode,
            'chapter' => $this->chapter,
            'section' => $this->section,
            'quotation_excerpt' => $this->quotation_excerpt,
            'evidence_strength' => $this->evidence_strength->value,
            'canon_classification' => $this->canon_classification->value,
            'review_status' => $this->review_status->value,
            'added_by_user_id' => $this->added_by_user_id,
        ];
    }
}
