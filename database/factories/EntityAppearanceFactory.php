<?php

namespace Database\Factories;

use App\Enums\AppearanceKind;
use App\Enums\AppearanceSignificance;
use App\Enums\CanonClassification;
use App\Enums\PublicationStatus;
use App\Models\EntityAppearance;
use App\Models\LoreEntity;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntityAppearance>
 */
class EntityAppearanceFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (EntityAppearance $appearance): void {
            $appearance->work->update(['universe_id' => $appearance->loreEntity->universe_id]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['lore_entity_id' => LoreEntity::factory(), 'work_id' => Work::factory(), 'season_id' => null, 'episode_id' => null, 'kind' => AppearanceKind::Appearance, 'significance' => AppearanceSignificance::Supporting, 'is_credited' => null, 'position' => 0, 'canon_classification' => CanonClassification::Unknown, 'notes' => null, 'status' => PublicationStatus::Draft, 'created_by' => null, 'updated_by' => null, 'published_at' => null, 'archived_at' => null, 'lock_version' => 0];
    }
}
