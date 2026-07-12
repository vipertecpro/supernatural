<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\SpoilerVisibility;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Season */
class SeasonResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $visibility = app(SpoilerVisibilityService::class)->decide($this->resource, $request->user());
        $redacted = in_array($visibility, [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true);

        return [
            'id' => $this->id,
            'type' => 'season',
            'work_id' => $this->work_id,
            'season_type' => $this->type->value,
            'number' => $this->number,
            'display_number' => $this->display_number,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $redacted ? null : $this->summary,
            'spoiler_redacted' => $redacted,
            'spoiler_visibility' => $visibility->value,
            'position' => $this->position,
            'original_release_date' => $this->original_release_date?->toDateString(),
            'release_date_precision' => $this->release_date_precision?->value,
            'status' => $this->status->value,
            'is_public' => $this->is_public,
            'version' => $this->lock_version,
            'published_at' => $this->published_at?->toIso8601String(),
            'archived_at' => $this->archived_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
