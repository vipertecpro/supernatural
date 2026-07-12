<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Bunker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Bunker */
class BunkerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'universe_id' => $this->universe_id, 'name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'rules_summary' => $this->rules_summary, 'visibility' => $this->visibility->value, 'status' => $this->status->value, 'requires_approval' => $this->requires_approval, 'requires_invitation' => $this->requires_invitation, 'default_locale' => $this->default_locale, 'spoiler_severity' => $this->spoiler_severity?->value, 'lock_version' => $this->lock_version, 'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($category) => ['id' => $category->id, 'key' => $category->key, 'name' => $category->name])->all()), 'published_at' => $this->published_at?->toISOString(), 'archived_at' => $this->archived_at?->toISOString(), 'created_at' => $this->created_at?->toISOString()];
    }
}
