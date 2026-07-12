<?php

namespace App\Http\Resources\Api\V1;

use App\Models\CommunityComment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CommunityComment */
class CommunityCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $spoilerVisibility = (string) ($this->getAttribute('viewer_spoiler_visibility') ?? 'visible');

        return ['id' => $this->id, 'post_id' => $this->post_id, 'parent_id' => $this->parent_id, 'root_id' => $this->root_id, 'depth' => $this->depth, 'author' => $this->author_user_id === null ? ['deleted' => true] : ['id' => $this->author_user_id, 'name' => $this->author?->name], 'body' => $this->status->value === 'published' && ! in_array($spoilerVisibility, ['redacted', 'hidden'], true) ? $this->body : null, 'spoiler_visibility' => $spoilerVisibility, 'status' => $this->status->value, 'is_edited' => $this->edited_at !== null, 'lock_version' => $this->lock_version, 'created_at' => $this->created_at?->toISOString()];
    }
}
