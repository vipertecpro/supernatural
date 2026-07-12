<?php

namespace Database\Factories;

use App\Models\ViewingOrder;
use App\Models\ViewingOrderItem;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ViewingOrderItem>
 */
class ViewingOrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'viewing_order_id' => ViewingOrder::factory(),
            'target_type' => 'work',
            'target_id' => fn (array $attributes): int => Work::factory()->create(['universe_id' => (int) ViewingOrder::query()->whereKey($attributes['viewing_order_id'])->value('universe_id')])->id,
            'position' => fake()->unique()->numberBetween(1, 100000),
            'is_optional' => false,
            'is_skippable' => false,
            'lock_version' => 0,
        ];
    }
}
