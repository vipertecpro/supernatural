<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\SpoilerVisibility;
use App\Models\WorkTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WorkTranslation */
class WorkTranslationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $visibility = app(SpoilerVisibilityService::class)->decide($this->resource, $request->user());
        $redacted = in_array($visibility, [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true);

        return [
            'locale' => $this->locale,
            'title' => $this->title,
            'short_title' => $this->short_title,
            'summary' => $redacted ? null : $this->summary,
            'synopsis' => $redacted ? null : $this->synopsis,
            'spoiler_visibility' => $visibility->value,
            'translated_from_locale' => $this->translated_from_locale,
            'status' => $this->status->value,
            'version' => $this->lock_version,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
