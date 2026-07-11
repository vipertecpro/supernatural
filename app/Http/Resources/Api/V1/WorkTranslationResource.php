<?php

namespace App\Http\Resources\Api\V1;

use App\Models\WorkTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WorkTranslation */
class WorkTranslationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'locale' => $this->locale,
            'title' => $this->title,
            'short_title' => $this->short_title,
            'summary' => $this->summary,
            'synopsis' => $this->synopsis,
            'translated_from_locale' => $this->translated_from_locale,
            'status' => $this->status->value,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
