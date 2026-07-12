<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\SpoilerVisibility;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Episode */
class EpisodeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $visibility = app(SpoilerVisibilityService::class)->decide($this->resource, $request->user());
        $redacted = in_array($visibility, [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true);

        return [
            'id' => $this->id,
            'type' => 'episode',
            'work_id' => $this->work_id,
            'season_id' => $this->season_id,
            'episode_type' => $this->type->value,
            'episode_number' => $this->episode_number,
            'display_number' => $this->display_number,
            'absolute_number' => $this->absolute_number,
            'production_code' => $this->production_code,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $redacted ? null : $this->summary,
            'synopsis' => $redacted ? null : $this->synopsis,
            'spoiler_redacted' => $redacted,
            'spoiler_visibility' => $visibility->value,
            'runtime_minutes' => $this->runtime_minutes,
            'original_release_date' => $this->original_release_date?->toDateString(),
            'release_date_precision' => $this->release_date_precision?->value,
            'position' => $this->position,
            'status' => $this->status->value,
            'is_public' => $this->is_public,
            'version' => $this->lock_version,
            'published_at' => $this->published_at?->toIso8601String(),
            'archived_at' => $this->archived_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
