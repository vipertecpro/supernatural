<?php

namespace Database\Factories;

use App\Models\TrendingSnapshot;
use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TrendingSnapshot> */
class TrendingSnapshotFactory extends Factory
{
    public function definition(): array
    {
        return ['universe_id' => Universe::factory(), 'subject_type' => 'work', 'subject_id' => 1, 'query_hash' => null, 'score' => 10, 'sample_size' => 10, 'window_started_at' => now()->subHour(), 'window_ended_at' => now()];
    }
}
