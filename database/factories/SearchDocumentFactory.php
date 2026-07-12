<?php

namespace Database\Factories;

use App\Enums\SearchDocumentType;
use App\Enums\SearchProjectionStatus;
use App\Models\SearchDocument;
use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SearchDocument> */
class SearchDocumentFactory extends Factory
{
    public function definition(): array
    {
        $title = 'The Ember Index '.fake()->unique()->numberBetween(10, 999999);

        return ['source_type' => 'work', 'source_id' => fake()->unique()->numberBetween(1, 999999), 'universe_id' => Universe::factory(), 'locale' => 'en', 'document_type' => SearchDocumentType::Work, 'canonical_title' => $title, 'localized_title' => $title, 'searchable_summary' => 'A fictional rights-safe search fixture.', 'normalized_text' => str($title.' fictional rights safe search fixture')->lower(), 'slug' => str($title)->slug(), 'route_key' => 'works/1', 'status' => SearchProjectionStatus::Active, 'visibility' => 'public', 'canon_classification' => 'unknown', 'spoiler_severity' => 'none', 'spoiler_boundary' => null, 'ranking_weight' => 50, 'popularity_score' => 0, 'projection_version' => 1, 'source_lock_version' => 0, 'facets' => [], 'safe_metadata' => [], 'freshness_at' => now(), 'indexed_at' => now(), 'archived_at' => null];
    }
}
