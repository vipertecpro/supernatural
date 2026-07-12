<?php

namespace Database\Factories;

use App\Enums\AppealDecisionType;
use App\Models\Appeal;
use App\Models\AppealDecision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AppealDecision> */
class AppealDecisionFactory extends Factory
{
    public function definition(): array
    {
        return ['appeal_id' => Appeal::factory(), 'reviewer_user_id' => User::factory(), 'type' => AppealDecisionType::Upheld, 'user_visible_explanation' => 'The original decision remains in effect after independent review.', 'decided_at' => now()];
    }
}
