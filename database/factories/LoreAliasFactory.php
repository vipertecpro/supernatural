<?php

namespace Database\Factories;

use App\Enums\LoreAliasType;
use App\Enums\PublicationStatus;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoreAlias>
 */
class LoreAliasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Glass Walker '.fake()->unique()->numberBetween(1, 999999);

        return ['lore_entity_id' => LoreEntity::factory(), 'name' => $name, 'normalized_name' => str($name)->lower()->squish(), 'type' => LoreAliasType::AlternateName, 'locale' => null, 'spoiler_sensitive' => false, 'status' => PublicationStatus::Draft, 'created_by' => null, 'updated_by' => null, 'published_at' => null, 'archived_at' => null, 'lock_version' => 0];
    }
}
