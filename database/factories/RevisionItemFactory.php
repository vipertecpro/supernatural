<?php

namespace Database\Factories;

use App\Enums\RevisionOperation;
use App\Models\EditorialRevision;
use App\Models\RevisionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RevisionItem>
 */
class RevisionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'editorial_revision_id' => EditorialRevision::factory(),
            'field' => 'runtime_minutes',
            'operation' => RevisionOperation::Replace,
            'previous_value_hash' => hash('sha256', 'null'),
            'proposed_value' => ['value' => fake()->numberBetween(20, 180)],
            'position' => 0,
            'validation_metadata' => ['registry_version' => 1],
        ];
    }
}
