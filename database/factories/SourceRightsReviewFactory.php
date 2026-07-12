<?php

namespace Database\Factories;

use App\Enums\RightsDecision;
use App\Enums\RightsUseType;
use App\Models\Source;
use App\Models\SourceRightsReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SourceRightsReview>
 */
class SourceRightsReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_id' => Source::factory(),
            'use_type' => RightsUseType::Quotation,
            'decision' => RightsDecision::Unknown,
            'basis' => 'No explicit permission evidence has been recorded.',
            'assessed_by_user_id' => User::factory(),
            'assessed_at' => now(),
        ];
    }

    public function allowed(): static
    {
        return $this->state(fn (): array => ['decision' => RightsDecision::Allowed, 'basis' => 'Written permission reference reviewed.']);
    }
}
