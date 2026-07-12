<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\SpoilerVisibility;
use App\Models\LoreRelationship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LoreRelationship */
class LoreRelationshipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $service = app(SpoilerVisibilityService::class);
        $visibility = $service->decide($this->resource, $request->user());
        $targetVisibility = $service->decide($this->targetEntity, $request->user());
        $redacted = in_array($visibility, [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true) || in_array($targetVisibility, [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true);

        return ['id' => $this->id, 'type' => 'lore_relationship', 'relationship_type' => new RelationshipTypeResource($this->whenLoaded('relationshipType')), 'source_entity_id' => $this->source_entity_id, 'target' => $redacted ? null : ['id' => $this->targetEntity->id, 'slug' => $this->targetEntity->slug, 'name' => $this->targetEntity->canonical_name, 'entity_type' => $this->targetEntity->type->value], 'canon_classification' => $this->canon_classification->value, 'confidence' => $redacted ? null : $this->confidence->value, 'qualifier' => $redacted ? null : $this->qualifier, 'start_boundary' => $redacted ? null : ['work_id' => $this->start_work_id, 'season_id' => $this->start_season_id, 'episode_id' => $this->start_episode_id], 'end_boundary' => $redacted ? null : ['work_id' => $this->end_work_id, 'season_id' => $this->end_season_id, 'episode_id' => $this->end_episode_id], 'starts_on' => $redacted ? null : $this->starts_on?->toDateString(), 'ends_on' => $redacted ? null : $this->ends_on?->toDateString(), 'spoiler_visibility' => $visibility->value, 'spoiler_redacted' => $redacted, 'status' => $this->status->value, 'version' => $this->lock_version, 'updated_at' => $this->updated_at?->toIso8601String()];
    }
}
