<?php

namespace App\Http\Resources\Api\V1;

use App\Models\UserViewingJourney;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserViewingJourney */
class ViewingJourneyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'universe_id' => $this->universe_id, 'viewing_order_id' => $this->viewing_order_id, 'rewatch_cycle_id' => $this->rewatch_cycle_id, 'status' => $this->status->value, 'current_item_id' => $this->current_item_id, 'current_work_id' => $this->current_work_id, 'current_season_id' => $this->current_season_id, 'current_episode_id' => $this->current_episode_id, 'visibility' => 'private', 'started_at' => $this->started_at?->toISOString(), 'paused_at' => $this->paused_at?->toISOString(), 'completed_at' => $this->completed_at?->toISOString(), 'abandoned_at' => $this->abandoned_at?->toISOString(), 'lock_version' => $this->lock_version, 'updated_at' => $this->updated_at?->toISOString()];
    }
}
