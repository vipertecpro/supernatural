<?php

namespace Database\Factories;

use App\Enums\ProgressEventType;
use App\Enums\ProgressSource;
use App\Enums\ProgressStatus;
use App\Models\ViewingProgress;
use App\Models\ViewingProgressEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ViewingProgressEvent>
 */
class ViewingProgressEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['viewing_progress_id' => ViewingProgress::factory(), 'user_id' => fn (array $attributes): int => (int) ViewingProgress::query()->whereKey($attributes['viewing_progress_id'])->value('user_id'), 'event_type' => ProgressEventType::PositionUpdated, 'new_status' => ProgressStatus::InProgress, 'source' => ProgressSource::Manual, 'occurred_at' => now()];
    }
}
