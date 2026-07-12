<?php

namespace Database\Factories;

use App\Enums\ReportPriority;
use App\Models\ReportCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReportCategory> */
class ReportCategoryFactory extends Factory
{
    public function definition(): array
    {
        $key = 'fixture_'.fake()->unique()->numberBetween(1000, 999999);

        return ['key' => $key, 'name' => str($key)->headline(), 'description' => 'A controlled rights-safe reporting category.', 'applicable_target_types' => ['universe', 'work', 'lore_entity', 'media_asset'], 'default_priority' => ReportPriority::Normal, 'evidence_required' => false, 'explanation_required' => true, 'rights_review_required' => false, 'safety_review_required' => false, 'appeals_supported' => true, 'is_active' => true, 'safe_metadata' => []];
    }
}
