<?php

namespace Database\Factories;

use App\Enums\CanonClassification;
use App\Enums\CitationEvidenceStrength;
use App\Enums\CitationReviewStatus;
use App\Models\Citation;
use App\Models\EditorialRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Citation>
 */
class CitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'citable_type' => 'editorial_revision',
            'citable_id' => EditorialRevision::factory(),
            'field_key' => null,
            'locator' => fake()->optional()->words(3, true),
            'quotation_excerpt' => fake()->optional()->sentence(8),
            'note' => fake()->optional()->sentence(),
            'evidence_strength' => CitationEvidenceStrength::Supporting,
            'canon_classification' => CanonClassification::Unknown,
            'added_by_user_id' => User::factory(),
            'review_status' => CitationReviewStatus::Pending,
        ];
    }
}
