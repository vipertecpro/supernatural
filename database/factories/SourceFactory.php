<?php

namespace Database\Factories;

use App\Enums\SourceType;
use App\Models\ContentLicense;
use App\Models\Source;
use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Source>
 */
class SourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'universe_id' => Universe::factory(),
            'content_license_id' => ContentLicense::factory(),
            'title' => fake()->sentence(5),
            'canonical_url' => fake()->unique()->url(),
            'source_type' => fake()->randomElement(SourceType::cases()),
            'publisher' => fake()->optional()->company(),
            'author' => fake()->optional()->name(),
            'published_at' => fake()->optional()->date(),
            'accessed_at' => fake()->date(),
            'attribution_text' => fake()->optional()->sentence(),
            'usage_notes' => fake()->optional()->paragraph(),
            'metadata' => [],
        ];
    }
}
