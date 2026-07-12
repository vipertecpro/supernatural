<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\PublicationStatus;
use App\Enums\SpoilerVisibility;
use App\Models\LoreEntity;
use App\Models\LoreEntityTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LoreEntity */
class LoreEntityResource extends JsonResource
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
        $locale = str((string) $request->query('locale', $request->header('Accept-Language', '')))->before(',')->replace('_', '-')->lower()->toString();
        $translation = $this->whenLoaded('translations', fn () => $this->translations->first(fn (LoreEntityTranslation $item): bool => $item->status === PublicationStatus::Published && $item->locale === $locale));
        $translated = $translation instanceof LoreEntityTranslation;

        return ['id' => $this->id, 'type' => 'lore_entity', 'universe_id' => $this->universe_id, 'entity_type' => $this->type->value, 'slug' => $this->slug, 'canonical_name' => $this->canonical_name, 'name' => $translated ? $translation->name : $this->canonical_name, 'locale' => $translated ? $translation->locale : $this->original_language, 'short_description' => $redacted ? null : ($translated ? $translation->short_description : $this->short_description), 'summary' => $redacted ? null : ($translated ? $translation->summary : $this->summary), 'canon_classification' => $this->canon_classification->value, 'visibility' => $this->visibility->value, 'status' => $this->status->value, 'spoiler_visibility' => $visibility->value, 'spoiler_redacted' => $redacted, 'version' => $this->lock_version, 'published_at' => $this->published_at?->toIso8601String(), 'updated_at' => $this->updated_at?->toIso8601String()];
    }
}
