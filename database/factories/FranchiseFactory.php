<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Franchise;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Franchise> */
class FranchiseFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->randomElement(['Midnight Archive', 'Ember Chronicle', 'Northstar Stories']).' '.fake()->unique()->numberBetween(10, 999999);

        return [
            'universe_id' => Universe::factory(),
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->optional()->sentence(),
            'status' => PublicationStatus::Draft,
            'is_public' => false,
            'position' => 0,
            'metadata' => [],
            'published_at' => null,
            'archived_at' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function trackedBy(User $user): static
    {
        return $this->state(fn (): array => ['created_by' => $user->id, 'updated_by' => $user->id]);
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
