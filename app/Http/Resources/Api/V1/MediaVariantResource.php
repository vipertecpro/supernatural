<?php

namespace App\Http\Resources\Api\V1;

use App\Models\MediaVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MediaVariant */
class MediaVariantResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'purpose' => $this->purpose->value, 'format' => $this->format, 'mime_type' => $this->mime_type, 'size_bytes' => $this->size_bytes, 'width' => $this->width, 'height' => $this->height, 'status' => $this->processing_status->value];
    }
}
