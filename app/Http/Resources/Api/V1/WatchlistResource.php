<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Watchlist */
class WatchlistResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'universe_id' => $this->universe_id, 'name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'visibility' => 'private', 'is_default' => $this->is_default, 'position' => $this->position, 'lock_version' => $this->lock_version, 'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item): array => ['id' => $item->id, 'target_type' => $item->target_type, 'target_id' => $item->target_id, 'position' => $item->position, 'added_at' => $item->added_at?->toISOString(), 'private_note' => $item->private_note])->all()), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
