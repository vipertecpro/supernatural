<?php

namespace Database\Factories;

use App\Enums\LoreEntityType;
use App\Models\LoreEntity;
use App\Models\PerformerDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformerDetail>
 */
class PerformerDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['lore_entity_id' => LoreEntity::factory()->type(LoreEntityType::Performer), 'professional_name' => 'Avery Sol '.fake()->unique()->numberBetween(1, 999999), 'production_notes' => null];
    }
}
