<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\PublicationStatus;
use App\Enums\SpoilerVisibility;
use App\Models\Work;
use App\Models\WorkTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Work */
class WorkResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $locale = str((string) $request->query('locale', $request->header('Accept-Language', '')))
            ->before(',')->replace('_', '-')->lower()->toString();
        $translation = $this->translations->first(
            fn (WorkTranslation $item): bool => $item->status === PublicationStatus::Published && $item->locale === $locale,
        );
        $hasTranslation = $this->translations->contains(
            fn (WorkTranslation $item): bool => $item->status === PublicationStatus::Published && $item->locale === $locale,
        );
        $publishedTranslations = $this->translations
            ->where('status', PublicationStatus::Published)
            ->values();
        $visibilityService = app(SpoilerVisibilityService::class);
        $visibility = $visibilityService->decide($this->resource, $request->user());
        $translationVisibility = $translation === null ? $visibility : $visibilityService->decide($translation, $request->user());
        $redacted = in_array($visibility, [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true);
        $translationRedacted = in_array($translationVisibility, [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true);

        return [
            'id' => $this->id,
            'type' => 'work',
            'universe_id' => $this->universe_id,
            'franchise_id' => $this->franchise_id,
            'work_type' => $this->type->value,
            'slug' => $this->slug,
            'canonical_title' => $this->original_title,
            'title' => $hasTranslation ? $translation->title : $this->original_title,
            'locale' => $hasTranslation ? $translation->locale : $this->original_language,
            'summary' => $hasTranslation
                ? ($translationRedacted ? null : ($translation->summary ?? ($redacted ? null : $this->summary)))
                : ($redacted ? null : $this->summary),
            'spoiler_redacted' => $redacted,
            'spoiler_visibility' => $visibility->value,
            'original_language' => $this->original_language,
            'runtime_minutes' => $this->runtime_minutes,
            'release_status' => $this->release_status->value,
            'canon_status' => $this->canon_status->value,
            'original_release_date' => $this->original_release_date?->toDateString(),
            'release_date_precision' => $this->release_date_precision?->value,
            'status' => $this->status->value,
            'is_public' => $this->is_public,
            'version' => $this->lock_version,
            'published_at' => $this->published_at?->toIso8601String(),
            'archived_at' => $this->archived_at?->toIso8601String(),
            'series_detail' => new SeriesDetailResource($this->whenLoaded('seriesDetail')),
            'translations' => WorkTranslationResource::collection($publishedTranslations),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
