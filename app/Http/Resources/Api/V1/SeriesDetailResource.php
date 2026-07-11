<?php

namespace App\Http\Resources\Api\V1;

use App\Models\SeriesDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SeriesDetail */
class SeriesDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'format' => $this->format->value,
            'status' => $this->series_status->value,
            'premiere_date' => $this->premiere_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'default_episode_duration' => $this->default_episode_duration,
            'default_episode_order' => $this->default_episode_order->value,
        ];
    }
}
