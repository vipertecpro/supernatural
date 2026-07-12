<?php

namespace App\Http\Resources\Api\V1;

use App\Models\RewatchCycle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RewatchCycle */
class RewatchCycleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'universe_id' => $this->universe_id, 'work_id' => $this->work_id, 'viewing_order_id' => $this->viewing_order_id, 'cycle_number' => $this->cycle_number, 'status' => $this->status->value, 'visibility' => 'private', 'started_at' => $this->started_at?->toISOString(), 'completed_at' => $this->completed_at?->toISOString(), 'abandoned_at' => $this->abandoned_at?->toISOString()];
    }
}
