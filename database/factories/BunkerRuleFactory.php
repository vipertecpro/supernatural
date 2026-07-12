<?php

namespace Database\Factories;

use App\Enums\BunkerRuleCategory;
use App\Models\Bunker;
use App\Models\BunkerRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BunkerRule>
 */
class BunkerRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['bunker_id' => Bunker::factory(), 'title' => fake()->sentence(3), 'description' => fake()->sentence(), 'category' => BunkerRuleCategory::Conduct, 'position' => fake()->unique()->numberBetween(1, 1000), 'is_active' => true, 'created_by' => User::factory(), 'updated_by' => null, 'lock_version' => 0];
    }
}
