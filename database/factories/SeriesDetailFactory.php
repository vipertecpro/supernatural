<?php

namespace Database\Factories;

use App\Enums\EpisodeOrder;
use App\Enums\SeriesFormat;
use App\Enums\SeriesStatus;
use App\Models\SeriesDetail;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SeriesDetail> */
class SeriesDetailFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'work_id' => Work::factory()->series(),
            'format' => SeriesFormat::Streaming,
            'series_status' => SeriesStatus::Ongoing,
            'premiere_date' => fake()->date(),
            'end_date' => null,
            'default_episode_duration' => 45,
            'default_episode_order' => EpisodeOrder::Aired,
            'metadata' => [],
        ];
    }
}
