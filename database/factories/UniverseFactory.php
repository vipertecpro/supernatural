<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Universe>
 */
class UniverseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->lexify('???? ???? ????');

        return [
            'name' => str($name)->title()->toString(),
            'slug' => str($name)->slug()->toString(),
            'description' => fake()->optional()->paragraph(),
            'status' => PublicationStatus::Draft,
            'is_public' => false,
            'metadata' => [],
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    /** Associate creator and updater tracking with a user. */
    public function trackedBy(User $user): static
    {
        return $this->state(fn (): array => [
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /** Mark the universe as published and public. */
    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PublicationStatus::Published,
            'is_public' => true,
        ]);
    }
}
