<?php

namespace Database\Factories;

use App\Models\LoreEntity;
use App\Models\TimelineEntry;
use App\Models\TimelineEntryEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimelineEntryEntity>
 */
class TimelineEntryEntityFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (TimelineEntryEntity $item): void {
            $item->loreEntity->update(['universe_id' => $item->timelineEntry->timeline->universe_id]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['timeline_entry_id' => TimelineEntry::factory(), 'lore_entity_id' => LoreEntity::factory(), 'role' => 'subject', 'position' => 0];
    }
}
