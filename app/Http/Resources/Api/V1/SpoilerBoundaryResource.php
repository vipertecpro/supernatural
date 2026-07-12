<?php

namespace App\Http\Resources\Api\V1;

use App\Models\SpoilerBoundary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SpoilerBoundary */
class SpoilerBoundaryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'constraint_id' => $this->spoiler_constraint_id,
            'target' => $this->whenLoaded('constraint', fn (): array => ['type' => $this->constraint->spoilerable_type, 'id' => $this->constraint->spoilerable_id]),
            'universe_id' => $this->whenLoaded('constraint', fn (): int => $this->constraint->universe_id),
            'work_id' => $this->work_id,
            'season_id' => $this->season_id,
            'episode_id' => $this->episode_id,
            'severity' => $this->whenLoaded('constraint', fn (): string => $this->constraint->severity->value),
            'classification_status' => $this->whenLoaded('constraint', fn (): string => $this->constraint->classification_status->value),
            'warning' => $this->whenLoaded('constraint', fn (): ?string => $this->constraint->warning),
        ];
    }
}
