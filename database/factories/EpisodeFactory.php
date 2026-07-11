<?php

namespace Database\Factories;

use App\Enums\EpisodeType;
use App\Enums\PublicationStatus;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Episode> */
class EpisodeFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 99999);
        $title = "The First Signal {$number}";

        return [
            'work_id' => Work::factory()->series(),
            'season_id' => null,
            'episode_number' => null,
            'display_number' => 'Special',
            'absolute_number' => $number,
            'production_code' => "EF-{$number}",
            'type' => EpisodeType::Special,
            'title' => $title,
            'slug' => str($title)->slug(),
            'summary' => fake()->sentence(),
            'synopsis' => fake()->paragraph(),
            'runtime_minutes' => 45,
            'original_release_date' => null,
            'release_date_precision' => null,
            'position' => $number,
            'status' => PublicationStatus::Draft,
            'is_public' => false,
            'metadata' => [],
            'published_at' => null,
            'archived_at' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function forSeason(Season $season, int $number = 1): static
    {
        return $this->state(fn (): array => [
            'work_id' => $season->work_id,
            'season_id' => $season->id,
            'episode_number' => $number,
            'display_number' => (string) $number,
            'type' => EpisodeType::Standard,
            'position' => $number,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PublicationStatus::Published,
            'is_public' => true,
            'published_at' => now(),
            'archived_at' => null,
        ]);
    }
}
