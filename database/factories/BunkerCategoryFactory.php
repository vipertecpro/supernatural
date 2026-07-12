<?php

namespace Database\Factories;

use App\Models\BunkerCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BunkerCategory>
 */
class BunkerCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->bothify('Category ####');

        return ['key' => str($name)->slug()->limit(60), 'name' => str($name)->title(), 'description' => fake()->sentence(), 'position' => fake()->numberBetween(0, 20), 'is_active' => true, 'metadata' => null];
    }
}
