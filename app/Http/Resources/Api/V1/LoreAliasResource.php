<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\SpoilerVisibility;
use App\Models\LoreAlias;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LoreAlias */
class LoreAliasResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $visibility = app(SpoilerVisibilityService::class)->decide($this->resource, $request->user());
        $redacted = $this->spoiler_sensitive && in_array($visibility, [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true);

        return ['id' => $this->id, 'type' => 'lore_alias', 'lore_entity_id' => $this->lore_entity_id, 'name' => $redacted ? null : $this->name, 'alias_type' => $this->type->value, 'locale' => $this->locale, 'spoiler_redacted' => $redacted, 'status' => $this->status->value, 'version' => $this->lock_version, 'updated_at' => $this->updated_at?->toIso8601String()];
    }
}
