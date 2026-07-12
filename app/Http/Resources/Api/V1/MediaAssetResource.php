<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\MediaProcessingStatus;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MediaAsset */
class MediaAssetResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'type' => 'media_asset', 'universe_id' => $this->universe_id, 'kind' => $this->kind->value, 'origin' => $this->origin->value,
            'filename' => $this->display_filename, 'mime_type' => $this->mime_type, 'size_bytes' => $this->size_bytes, 'width' => $this->width, 'height' => $this->height,
            'duration_seconds' => $this->duration_seconds, 'alt_text' => $this->alt_text, 'caption' => $this->caption, 'attribution_text' => $this->attribution_text,
            'copyright_owner' => $this->copyright_owner, 'status' => $this->status->value, 'moderation_status' => $this->moderation_status->value,
            'processing_status' => $this->processing_status->value, 'visibility' => $this->visibility->value, 'version' => $this->lock_version,
            'variants' => MediaVariantResource::collection($this->whenLoaded('variants', fn () => $this->variants->where('processing_status', MediaProcessingStatus::Ready)->values())),
            'published_at' => $this->published_at?->toIso8601String(), 'archived_at' => $this->archived_at?->toIso8601String(), 'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
