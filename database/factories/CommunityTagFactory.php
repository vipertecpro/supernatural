<?php

namespace Database\Factories;

use App\Models\CommunityTag;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityTag>
 */
class CommunityTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->bothify('topic ####');

        return ['universe_id' => Universe::factory(), 'created_by' => User::factory(), 'normalized_name' => str($name)->lower()->squish(), 'display_name' => str($name)->title(), 'slug' => str($name)->slug(), 'status' => 'active'];
    }
}
