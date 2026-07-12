<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ExternalEmbed;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ExternalEmbed */
class ExternalEmbedResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'type' => 'external_embed', 'universe_id' => $this->universe_id, 'provider' => $this->provider->value,
            'provider_content_id' => $this->provider_content_id, 'canonical_url' => $this->canonical_url, 'embed_url' => $this->embed_url,
            'kind' => $this->kind->value, 'title' => $this->title, 'description' => $this->description, 'thumbnail_url' => $this->thumbnail_url,
            'creator' => $this->creator, 'publisher' => $this->publisher, 'attribution_text' => $this->attribution_text, 'status' => $this->status->value,
            'moderation_status' => $this->moderation_status->value, 'visibility' => $this->visibility->value, 'version' => $this->lock_version,
            'published_at' => $this->published_at?->toIso8601String(), 'archived_at' => $this->archived_at?->toIso8601String(), 'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
