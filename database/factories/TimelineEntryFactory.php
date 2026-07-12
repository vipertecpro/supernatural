<?php

namespace Database\Factories;

use App\Enums\CanonClassification;
use App\Enums\PublicationStatus;
use App\Enums\RelationshipConfidence;
use App\Enums\TimelineEntryType;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimelineEntry>
 */
class TimelineEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['timeline_id' => Timeline::factory(), 'type' => TimelineEntryType::EditorialMarker, 'work_id' => null, 'season_id' => null, 'episode_id' => null, 'lore_event_entity_id' => null, 'lore_relationship_id' => null, 'title' => 'The First Signal '.fake()->unique()->numberBetween(1, 999999), 'summary' => fake()->sentence(), 'sort_key' => fake()->unique()->numberBetween(1, 999999), 'sequence_number' => null, 'in_universe_date' => null, 'date_precision' => null, 'relative_order' => 'after the opening marker', 'canon_classification' => CanonClassification::Unknown, 'confidence' => RelationshipConfidence::Unknown, 'status' => PublicationStatus::Draft, 'created_by' => null, 'updated_by' => null, 'published_at' => null, 'archived_at' => null, 'lock_version' => 0];
    }
}
