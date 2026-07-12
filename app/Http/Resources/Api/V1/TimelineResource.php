<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\SpoilerVisibility;
use App\Models\Timeline;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Timeline */
class TimelineResource extends JsonResource
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

        return ['id' => $this->id, 'type' => 'timeline', 'universe_id' => $this->universe_id, 'lore_entity_id' => $this->lore_entity_id, 'work_id' => $this->work_id, 'name' => $this->name, 'slug' => $this->slug, 'timeline_type' => $this->type->value, 'description' => $redacted ? null : $this->description, 'canon_classification' => $this->canon_classification->value, 'visibility' => $this->visibility->value, 'status' => $this->status->value, 'spoiler_visibility' => $visibility->value, 'version' => $this->lock_version, 'published_at' => $this->published_at?->toIso8601String(), 'updated_at' => $this->updated_at?->toIso8601String()];
    }
}
