<?php

namespace Database\Factories;

use App\Enums\PersonalVisibility;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Watchlist>
 */
class WatchlistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->sentence(2);

        return ['user_id' => User::factory(), 'name' => ucfirst($name), 'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 999999), 'visibility' => PersonalVisibility::Private, 'is_default' => false, 'position' => 0, 'lock_version' => 0];
    }
}
