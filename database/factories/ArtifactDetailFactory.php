<?php

namespace Database\Factories;

use App\Enums\LoreEntityType;
use App\Models\ArtifactDetail;
use App\Models\LoreEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtifactDetail>
 */
class ArtifactDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['lore_entity_id' => LoreEntity::factory()->type(LoreEntityType::Artifact), 'category' => 'archive', 'function' => 'Stores fictional signals.', 'usage_constraints' => 'Operates only within its fictional setting.'];
    }
}
