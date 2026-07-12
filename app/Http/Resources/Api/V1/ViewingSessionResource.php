<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ViewingSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ViewingSession */
class ViewingSessionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'journey_id' => $this->user_viewing_journey_id, 'rewatch_cycle_id' => $this->rewatch_cycle_id, 'work_id' => $this->work_id, 'season_id' => $this->season_id, 'episode_id' => $this->episode_id, 'status' => $this->status->value, 'source' => $this->source->value, 'started_at' => $this->started_at?->toISOString(), 'last_activity_at' => $this->last_activity_at?->toISOString(), 'ended_at' => $this->ended_at?->toISOString(), 'starting_position_seconds' => $this->starting_position_seconds, 'ending_position_seconds' => $this->ending_position_seconds, 'watched_seconds' => $this->watched_seconds, 'lock_version' => $this->lock_version];
    }
}
