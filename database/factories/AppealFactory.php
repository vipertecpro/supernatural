<?php

namespace Database\Factories;

use App\Enums\AppealStatus;
use App\Models\Appeal;
use App\Models\ModerationAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Appeal> */
class AppealFactory extends Factory
{
    public function definition(): array
    {
        return ['appellant_user_id' => User::factory(), 'moderation_action_id' => ModerationAction::factory(), 'moderation_case_id' => fn (array $attributes): int => (int) ModerationAction::query()->whereKey($attributes['moderation_action_id'])->value('moderation_case_id'), 'status' => AppealStatus::Submitted, 'active_key' => 'active', 'explanation' => 'A concise fictional appeal with relevant new context.', 'submitted_at' => now(), 'lock_version' => 0];
    }
}
