<?php

namespace App\Http\Resources\Api\V1;

use App\Models\CommunityPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CommunityPost */
class CommunityPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $spoilerVisibility = (string) ($this->getAttribute('viewer_spoiler_visibility') ?? 'visible');
        $redacted = in_array($spoilerVisibility, ['redacted', 'hidden'], true);

        return ['id' => $this->id, 'author' => $this->author_user_id === null ? ['deleted' => true] : ['id' => $this->author_user_id, 'name' => $this->author?->name], 'bunker_id' => $this->bunker_id, 'universe_id' => $this->universe_id, 'reference' => $redacted || $this->reference_type === null ? null : ['type' => $this->reference_type, 'id' => $this->reference_id], 'title' => $redacted ? null : $this->title, 'body' => $redacted ? null : $this->body, 'spoiler_visibility' => $spoilerVisibility, 'status' => $this->status->value, 'visibility' => $this->visibility->value, 'comments_enabled' => $this->comments_enabled, 'is_edited' => $this->edited_at !== null, 'is_locked' => $this->locked_at !== null, 'lock_version' => $this->lock_version, 'polls' => $redacted ? [] : CommunityPollResource::collection($this->whenLoaded('polls')), 'published_at' => $this->published_at?->toISOString(), 'created_at' => $this->created_at?->toISOString()];
    }
}
