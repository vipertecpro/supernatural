<?php

namespace App\Http\Resources\Api\V1;

use App\Models\MediaAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MediaAttachment */
class MediaAttachmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'type' => 'media_attachment', 'target_type' => $this->attachable_type, 'target_id' => $this->attachable_id,
            'role' => $this->role->value, 'position' => $this->position, 'is_primary' => $this->is_primary, 'locale' => $this->locale,
            'caption' => $this->caption_override, 'alt_text' => $this->alt_text_override, 'status' => $this->status->value, 'version' => $this->lock_version,
            'media' => $this->mediaAsset !== null ? (new MediaAssetResource($this->mediaAsset))->resolve($request) : (new ExternalEmbedResource($this->externalEmbed))->resolve($request),
        ];
    }
}
