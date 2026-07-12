<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\SpoilerVisibility;
use App\Models\EntityAppearance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EntityAppearance */
class EntityAppearanceResource extends JsonResource
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

        return ['id' => $this->id, 'type' => 'entity_appearance', 'lore_entity_id' => $this->lore_entity_id, 'work_id' => $this->work_id, 'season_id' => $redacted ? null : $this->season_id, 'episode_id' => $redacted ? null : $this->episode_id, 'appearance_kind' => $this->kind->value, 'significance' => $redacted ? null : $this->significance->value, 'is_credited' => $redacted ? null : $this->is_credited, 'position' => $this->position, 'canon_classification' => $this->canon_classification->value, 'notes' => $redacted ? null : $this->notes, 'spoiler_visibility' => $visibility->value, 'status' => $this->status->value, 'version' => $this->lock_version, 'updated_at' => $this->updated_at?->toIso8601String()];
    }
}
