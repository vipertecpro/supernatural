<?php

namespace App\Http\Resources\Api\V1;

use App\Models\PersonalNote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PersonalNote */
class PersonalNoteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'universe_id' => $this->universe_id, 'target_type' => $this->target_type, 'target_id' => $this->target_id, 'title' => $this->title, 'body' => $this->when($request->routeIs('api.v1.me.notes.show'), $this->body), 'format' => 'plain_text', 'visibility' => 'private', 'is_pinned' => $this->is_pinned, 'is_spoiler_sensitive' => $this->is_spoiler_sensitive, 'lock_version' => $this->lock_version, 'updated_at' => $this->updated_at?->toISOString()];
    }
}
