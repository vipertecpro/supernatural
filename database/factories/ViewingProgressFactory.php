<?php

namespace Database\Factories;

use App\Enums\ProgressSource;
use App\Enums\ProgressStatus;
use App\Models\Universe;
use App\Models\User;
use App\Models\ViewingProgress;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ViewingProgress>
 */
class ViewingProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'universe_id' => Universe::factory(),
            'work_id' => fn (array $attributes): int => Work::factory()->create(['universe_id' => $attributes['universe_id']])->id,
            'season_id' => null,
            'episode_id' => null,
            'scope_type' => fn (array $attributes): string => $attributes['episode_id'] !== null ? 'episode' : ($attributes['season_id'] !== null ? 'season' : 'work'),
            'scope_key' => function (array $attributes): string {
                $type = $attributes['episode_id'] !== null ? 'episode' : ($attributes['season_id'] !== null ? 'season' : 'work');

                return $type.':'.$attributes[$type.'_id'];
            },
            'cycle_key' => 0,
            'status' => ProgressStatus::Completed,
            'progress_basis_points' => 10000,
            'started_at' => now(),
            'last_watched_at' => now(),
            'completed_at' => now(),
            'source' => ProgressSource::Manual,
            'is_manual_override' => false,
            'is_legacy_projection' => false,
            'lock_version' => 0,
        ];
    }
}
