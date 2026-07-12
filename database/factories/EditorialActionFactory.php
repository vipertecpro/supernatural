<?php

namespace Database\Factories;

use App\Enums\EditorialActionType;
use App\Models\EditorialAction;
use App\Models\EditorialRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EditorialAction>
 */
class EditorialActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'editorial_revision_id' => EditorialRevision::factory(),
            'actor_user_id' => User::factory(),
            'type' => EditorialActionType::Submitted,
            'public_explanation' => fake()->optional()->sentence(),
            'private_note' => null,
            'acted_at' => now(),
        ];
    }
}
