<?php

namespace Database\Factories;

use App\Enums\ModerationAssignmentStatus;
use App\Models\ModerationCase;
use App\Models\ModerationCaseAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ModerationCaseAssignment> */
class ModerationCaseAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return ['moderation_case_id' => ModerationCase::factory(), 'moderator_user_id' => User::factory(), 'assigned_by_user_id' => User::factory(), 'role' => 'primary', 'status' => ModerationAssignmentStatus::Assigned, 'active_primary_key' => 'primary', 'assigned_at' => now()];
    }
}
