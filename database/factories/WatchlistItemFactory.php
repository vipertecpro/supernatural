<?php

namespace Database\Factories;

use App\Models\Watchlist;
use App\Models\WatchlistItem;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WatchlistItem>
 */
class WatchlistItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['watchlist_id' => Watchlist::factory(), 'target_type' => 'work', 'target_id' => Work::factory(), 'position' => fake()->unique()->numberBetween(1, 100000), 'added_at' => now()];
    }
}
