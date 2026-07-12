<?php

namespace Database\Factories;

use App\Enums\EditorialRevisionStatus;
use App\Models\EditorialRevision;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EditorialRevision>
 */
class EditorialRevisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'revisable_type' => 'work',
            'revisable_id' => Work::factory(),
            'author_user_id' => User::factory(),
            'parent_revision_id' => null,
            'revision_number' => 1,
            'base_version' => 0,
            'status' => EditorialRevisionStatus::Draft,
            'summary' => fake()->sentence(),
            'metadata' => ['schema_version' => 1],
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (): array => ['status' => EditorialRevisionStatus::Submitted, 'submitted_at' => now()]);
    }

    public function approved(): static
    {
        return $this->state(fn (): array => ['status' => EditorialRevisionStatus::Approved, 'submitted_at' => now()->subHour(), 'decided_at' => now()]);
    }

    public function changesRequested(): static
    {
        return $this->state(fn (): array => ['status' => EditorialRevisionStatus::ChangesRequested, 'submitted_at' => now()->subHour(), 'decided_at' => now()]);
    }
}
