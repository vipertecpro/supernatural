<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ViewingOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ViewingOrder */
class ViewingOrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'universe_id' => $this->universe_id, 'franchise_id' => $this->franchise_id, 'name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'type' => $this->type->value, 'is_default' => $this->is_default, 'locale' => $this->locale, 'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item): array => ['id' => $item->id, 'target_type' => $item->target_type, 'target_id' => $item->target_id, 'position' => $item->position, 'group_label' => $item->group_label, 'display_title' => $item->display_title, 'explanation' => null, 'is_optional' => $item->is_optional, 'is_skippable' => $item->is_skippable])->all()), 'published_at' => $this->published_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
