<?php

namespace Database\Factories;

use App\Enums\CanonStatus;
use App\Enums\PublicationStatus;
use App\Enums\WorkReleaseStatus;
use App\Enums\WorkType;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Work> */
class WorkFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = fake()->randomElement(['The Ember Files', 'The First Signal', 'Glass Horizon']).' '.fake()->unique()->numberBetween(10, 999999);

        return [
            'universe_id' => Universe::factory(),
            'franchise_id' => null,
            'type' => WorkType::Film,
            'slug' => str($title)->slug(),
            'original_title' => $title,
            'original_language' => 'en',
            'summary' => fake()->sentence(),
            'runtime_minutes' => 90,
            'release_status' => WorkReleaseStatus::Released,
            'canon_status' => CanonStatus::Unknown,
            'original_release_date' => fake()->date(),
            'release_date_precision' => null,
            'status' => PublicationStatus::Draft,
            'is_public' => false,
            'metadata' => [],
            'published_at' => null,
            'archived_at' => null,
            'lock_version' => 0,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function trackedBy(User $user): static
    {
        return $this->state(fn (): array => ['created_by' => $user->id, 'updated_by' => $user->id]);
    }

    public function series(): static
    {
        return $this->state(fn (): array => ['type' => WorkType::Series, 'runtime_minutes' => null]);
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
