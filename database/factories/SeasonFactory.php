<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Enums\SeasonType;
use App\Models\Season;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Season> */
class SeasonFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 9999);

        return [
            'work_id' => Work::factory()->series(),
            'type' => SeasonType::Season,
            'number' => $number,
            'display_number' => (string) $number,
            'title' => "Season {$number}",
            'slug' => "season-{$number}",
            'summary' => fake()->sentence(),
            'position' => $number,
            'original_release_date' => null,
            'release_date_precision' => null,
            'status' => PublicationStatus::Draft,
            'is_public' => false,
            'metadata' => [],
            'published_at' => null,
            'archived_at' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
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

    public function specials(): static
    {
        return $this->state(fn (): array => [
            'type' => SeasonType::Specials,
            'number' => null,
            'display_number' => 'Specials',
            'title' => 'Specials',
            'slug' => 'specials',
            'position' => 0,
        ]);
    }
}
