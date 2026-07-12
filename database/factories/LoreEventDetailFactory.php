<?php

namespace Database\Factories;

use App\Enums\DatePrecision;
use App\Enums\LoreEntityType;
use App\Models\LoreEntity;
use App\Models\LoreEventDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoreEventDetail>
 */
class LoreEventDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['lore_entity_id' => LoreEntity::factory()->type(LoreEntityType::Event), 'event_type' => 'discovery', 'occurred_on' => fake()->date(), 'date_precision' => DatePrecision::Day, 'work_id' => null, 'season_id' => null, 'episode_id' => null];
    }
}
