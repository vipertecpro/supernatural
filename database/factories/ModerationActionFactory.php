<?php

namespace Database\Factories;

use App\Enums\ModerationActionType;
use App\Models\ModerationAction;
use App\Models\ModerationCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ModerationAction> */
class ModerationActionFactory extends Factory
{
    public function definition(): array
    {
        return ['moderation_case_id' => ModerationCase::factory(), 'actor_user_id' => User::factory(), 'type' => ModerationActionType::WarningIssued, 'target_user_id' => User::factory(), 'reason_code' => 'policy_warning', 'user_visible_explanation' => 'A policy warning was applied after review.', 'effective_at' => now(), 'safe_metadata' => []];
    }
}
