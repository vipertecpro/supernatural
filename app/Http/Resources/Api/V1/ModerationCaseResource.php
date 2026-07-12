<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ModerationCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ModerationCase */
class ModerationCaseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->public_id, 'target' => ['type' => $this->target_type, 'id' => $this->target_id], 'subject_user_id' => $this->subject_user_id, 'status' => $this->status->value, 'priority' => $this->priority->value, 'resolution_code' => $this->resolution_code, 'user_visible_summary' => $this->user_visible_summary, 'private_internal_summary' => $this->private_internal_summary, 'lock_version' => $this->lock_version, 'reports' => ReportResource::collection($this->whenLoaded('reports'))->resolve($request), 'assignments' => $this->relationLoaded('assignments') ? $this->assignments->map(fn ($assignment): array => ['id' => $assignment->id, 'moderator_user_id' => $assignment->moderator_user_id, 'role' => $assignment->role, 'status' => $assignment->status->value, 'assigned_at' => $assignment->assigned_at?->toISOString(), 'due_at' => $assignment->due_at?->toISOString()])->all() : [], 'actions' => $this->relationLoaded('actions') ? $this->actions->map(fn ($action): array => ['id' => $action->id, 'type' => $action->type->value, 'target_user_id' => $action->target_user_id, 'target' => $action->target_content_type === null ? null : ['type' => $action->target_content_type, 'id' => $action->target_content_id], 'reason_code' => $action->reason_code, 'user_visible_explanation' => $action->user_visible_explanation, 'effective_at' => $action->effective_at?->toISOString(), 'expires_at' => $action->expires_at?->toISOString()])->all() : [], 'opened_at' => $this->opened_at?->toISOString(), 'closed_at' => $this->closed_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
