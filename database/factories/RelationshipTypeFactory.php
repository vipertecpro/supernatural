<?php

namespace Database\Factories;

use App\Enums\RelationshipDirection;
use App\Models\RelationshipType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RelationshipType>
 */
class RelationshipTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $key = 'related-to-'.fake()->unique()->numberBetween(1, 999999);

        return ['key' => $key, 'forward_label' => 'related to', 'inverse_label' => 'related to', 'direction' => RelationshipDirection::Undirected, 'is_symmetric' => true, 'is_transitive' => false, 'allows_self' => false, 'allows_duplicates' => false, 'allows_temporal_bounds' => true, 'requires_catalog_boundary' => false, 'requires_citation' => true, 'requires_spoiler_classification' => true, 'requires_editorial_approval' => true, 'is_active' => true, 'metadata' => []];
    }
}
