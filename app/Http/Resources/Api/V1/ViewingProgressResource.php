<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ViewingProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ViewingProgress */
class ViewingProgressResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'scope_type' => $this->scope_type, 'work_id' => $this->work_id, 'season_id' => $this->season_id, 'episode_id' => $this->episode_id, 'journey_id' => $this->user_viewing_journey_id, 'next_viewing_order_item_id' => $this->whenLoaded('journey', fn () => $this->journey?->current_item_id), 'rewatch_cycle_id' => $this->rewatch_cycle_id, 'status' => $this->status->value, 'progress_basis_points' => $this->progress_basis_points, 'runtime_position_seconds' => $this->runtime_position_seconds, 'started_at' => $this->started_at?->toISOString(), 'last_watched_at' => $this->last_watched_at?->toISOString(), 'completed_at' => $this->completed_at?->toISOString(), 'source' => $this->source->value, 'is_manual_override' => $this->is_manual_override, 'lock_version' => $this->lock_version];
    }
}
