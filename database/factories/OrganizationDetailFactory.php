<?php

namespace Database\Factories;

use App\Enums\LoreEntityType;
use App\Models\LoreEntity;
use App\Models\OrganizationDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationDetail>
 */
class OrganizationDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['lore_entity_id' => LoreEntity::factory()->type(LoreEntityType::Organization), 'organization_type' => 'order', 'lifecycle_status' => 'active', 'founded_description' => 'Founded during the fictional First Signal era.'];
    }
}
