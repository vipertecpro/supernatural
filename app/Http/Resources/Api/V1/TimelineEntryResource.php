<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\SpoilerVisibility;
use App\Models\TimelineEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TimelineEntry */
class TimelineEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $visibility = app(SpoilerVisibilityService::class)->decide($this->resource, $request->user());
        $redacted = in_array($visibility, [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true);

        return ['id' => $this->id, 'type' => 'timeline_entry', 'timeline_id' => $this->timeline_id, 'entry_type' => $this->type->value, 'title' => $redacted ? 'Spoiler-protected timeline entry' : $this->title, 'summary' => $redacted ? null : $this->summary, 'sort_key' => $this->sort_key, 'sequence_number' => $this->sequence_number, 'in_universe_date' => $redacted ? null : $this->in_universe_date?->toDateString(), 'date_precision' => $redacted ? null : $this->date_precision?->value, 'relative_order' => $redacted ? null : $this->relative_order, 'work_id' => $redacted ? null : $this->work_id, 'season_id' => $redacted ? null : $this->season_id, 'episode_id' => $redacted ? null : $this->episode_id, 'lore_event_entity_id' => $redacted ? null : $this->lore_event_entity_id, 'lore_relationship_id' => $redacted ? null : $this->lore_relationship_id, 'entity_ids' => $redacted ? [] : $this->whenLoaded('entities', fn () => $this->entities->pluck('id')->all()), 'canon_classification' => $this->canon_classification->value, 'confidence' => $redacted ? null : $this->confidence->value, 'spoiler_visibility' => $visibility->value, 'status' => $this->status->value, 'version' => $this->lock_version, 'updated_at' => $this->updated_at?->toIso8601String()];
    }
}
