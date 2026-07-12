<?php

namespace App\Http\Resources\Api\V1;

use App\Models\EditorialRevision;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EditorialRevision */
class EditorialRevisionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => 'editorial_revision',
            'target' => ['type' => $this->revisable_type, 'id' => $this->revisable_id],
            'author_user_id' => $this->author_user_id,
            'parent_revision_id' => $this->parent_revision_id,
            'revision_number' => $this->revision_number,
            'base_version' => $this->base_version,
            'target_version' => $this->whenLoaded('revisable', fn (): int => (int) $this->revisable->getAttribute('lock_version')),
            'status' => $this->status->value,
            'summary' => $this->summary,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item): array => [
                'id' => $item->id,
                'field' => $item->field,
                'operation' => $item->operation->value,
                'proposed_value' => $item->proposed_value['value'] ?? null,
                'position' => $item->position,
            ])),
            'blocks' => $this->whenLoaded('blocks', fn () => $this->blocks->map(fn ($block): array => [
                'id' => $block->id,
                'field' => $block->field,
                'locale' => $block->locale,
                'proposed_text' => $block->proposed_text,
                'format' => $block->format,
                'position' => $block->position,
            ])),
            'assignments' => $this->whenLoaded('assignments', fn () => $this->assignments->map(fn ($assignment): array => [
                'id' => $assignment->id,
                'reviewer_user_id' => $assignment->reviewer_user_id,
                'status' => $assignment->status->value,
                'assigned_at' => $assignment->assigned_at->toIso8601String(),
                'due_at' => $assignment->due_at?->toDateString(),
            ])),
            'decisions' => $this->whenLoaded('actions', fn () => $this->actions->filter(fn ($action): bool => in_array($action->type->value, ['changes_requested', 'approved', 'rejected'], true))->map(fn ($action): array => [
                'id' => $action->id,
                'reviewer_user_id' => $action->actor_user_id,
                'type' => $action->type->value,
                'public_explanation' => $action->public_explanation,
                'source_result' => $action->source_result?->value,
                'rights_result' => $action->rights_result?->value,
                'spoiler_result' => $action->spoiler_result?->value,
                'acted_at' => $action->acted_at->toIso8601String(),
            ])->values()),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'review_started_at' => $this->review_started_at?->toIso8601String(),
            'decided_at' => $this->decided_at?->toIso8601String(),
            'applied_at' => $this->applied_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
