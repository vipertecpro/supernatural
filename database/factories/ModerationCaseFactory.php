<?php

namespace Database\Factories;

use App\Enums\ModerationCaseStatus;
use App\Enums\ReportPriority;
use App\Models\ModerationCase;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ModerationCase> */
class ModerationCaseFactory extends Factory
{
    public function definition(): array
    {
        return ['public_id' => (string) Str::ulid(), 'target_type' => 'universe', 'target_id' => Universe::factory(), 'subject_user_id' => null, 'status' => ModerationCaseStatus::Open, 'priority' => ReportPriority::Normal, 'opened_by_user_id' => User::factory(), 'opened_at' => now(), 'safe_metadata' => [], 'lock_version' => 0];
    }

    public function closed(): static
    {
        return $this->state(fn (): array => ['status' => ModerationCaseStatus::Closed, 'resolution_code' => 'resolved', 'user_visible_summary' => 'The review is complete.', 'decision_at' => now(), 'closed_at' => now()]);
    }
}
