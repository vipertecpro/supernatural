<?php

namespace Database\Factories;

use App\Models\EntityTaxonomy;
use App\Models\EntityTaxonomyItem;
use App\Models\LoreEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntityTaxonomyItem>
 */
class EntityTaxonomyItemFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (EntityTaxonomyItem $item): void {
            $item->loreEntity->update(['universe_id' => $item->taxonomy->universe_id]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['entity_taxonomy_id' => EntityTaxonomy::factory(), 'lore_entity_id' => LoreEntity::factory(), 'position' => 0];
    }
}
