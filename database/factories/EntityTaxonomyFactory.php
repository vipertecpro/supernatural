<?php

namespace Database\Factories;

use App\Enums\TaxonomyScope;
use App\Models\EntityTaxonomy;
use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntityTaxonomy>
 */
class EntityTaxonomyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $key = 'classification-'.fake()->unique()->numberBetween(1, 999999);

        return ['universe_id' => Universe::factory(), 'scope' => TaxonomyScope::General, 'key' => $key, 'name' => str($key)->headline(), 'description' => fake()->sentence(), 'is_active' => true];
    }
}
