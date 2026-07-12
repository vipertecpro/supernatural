<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Appeal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appeal */
class AppealResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'moderation_action_id' => $this->moderation_action_id, 'status' => $this->status->value, 'explanation' => $this->explanation, 'submitted_at' => $this->submitted_at?->toISOString(), 'review_started_at' => $this->review_started_at?->toISOString(), 'decided_at' => $this->decided_at?->toISOString(), 'withdrawn_at' => $this->withdrawn_at?->toISOString(), 'lock_version' => $this->lock_version, 'decision' => $this->relationLoaded('decision') && $this->decision !== null ? ['type' => $this->decision->type->value, 'user_visible_explanation' => $this->decision->user_visible_explanation, 'decided_at' => $this->decision->decided_at?->toISOString()] : null];
    }
}
