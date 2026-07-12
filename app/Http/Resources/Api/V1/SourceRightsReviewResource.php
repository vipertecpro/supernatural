<?php

namespace App\Http\Resources\Api\V1;

use App\Models\SourceRightsReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SourceRightsReview */
class SourceRightsReviewResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source_id' => $this->source_id,
            'use_type' => $this->use_type->value,
            'decision' => $this->decision->value,
            'basis' => $this->basis,
            'content_license_id' => $this->content_license_id,
            'rights_holder' => $this->rights_holder,
            'assessed_by_user_id' => $this->assessed_by_user_id,
            'reviewed_by_user_id' => $this->reviewed_by_user_id,
            'supersedes_review_id' => $this->supersedes_review_id,
            'assessed_at' => $this->assessed_at->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'effective' => $this->isEffective(),
        ];
    }
}
