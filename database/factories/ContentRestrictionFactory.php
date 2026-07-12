<?php

namespace Database\Factories;

use App\Enums\ContentRestrictionType;
use App\Enums\RestrictionStatus;
use App\Models\ContentRestriction;
use App\Models\ModerationAction;
use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ContentRestriction> */
class ContentRestrictionFactory extends Factory
{
    public function definition(): array
    {
        return ['target_type' => 'universe', 'target_id' => Universe::factory(), 'moderation_action_id' => ModerationAction::factory(), 'type' => ContentRestrictionType::HiddenFromPublic, 'status' => RestrictionStatus::Active, 'effective_at' => now(), 'expires_at' => now()->addDays(7), 'reason_code' => 'policy_review', 'public_explanation' => 'This content is temporarily unavailable while it is reviewed.'];
    }
}
