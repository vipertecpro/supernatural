<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Rating */
class RatingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'universe_id' => $this->universe_id, 'target_type' => $this->target_type, 'target_id' => $this->target_id, 'rating' => $this->rating, 'updated_at' => $this->updated_at?->toISOString()];
    }
}
