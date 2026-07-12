<?php

namespace App\Http\Resources\Api\V1;

use App\Models\CommunityPoll;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CommunityPoll */
class CommunityPollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewerId = $request->user()?->id;
        $hasVoted = $viewerId !== null && $this->votes()->where('user_id', $viewerId)->exists();
        $showResults = $this->results_visibility->value === 'always' || ($this->results_visibility->value === 'after_vote' && $hasVoted) || $this->status->value === 'closed';

        return ['id' => $this->id, 'question' => $this->question, 'type' => $this->type->value, 'maximum_choices' => $this->maximum_choices, 'status' => $this->status->value, 'results_visibility' => $this->results_visibility->value, 'has_voted' => $hasVoted, 'options' => $this->whenLoaded('options', fn () => $this->options->map(fn ($option) => ['id' => $option->id, 'text' => $option->text, 'position' => $option->position, 'votes' => $showResults ? $option->votes()->count() : null])->all()), 'lock_version' => $this->lock_version, 'closes_at' => $this->closes_at?->toISOString(), 'closed_at' => $this->closed_at?->toISOString()];
    }
}
