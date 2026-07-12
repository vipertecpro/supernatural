<?php

namespace Database\Factories;

use App\Enums\ReportPriority;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Report> */
class ReportFactory extends Factory
{
    public function definition(): array
    {
        return ['reporter_user_id' => User::factory(), 'report_category_id' => ReportCategory::factory(), 'target_type' => 'universe', 'target_id' => Universe::factory(), 'status' => ReportStatus::Submitted, 'priority' => ReportPriority::Normal, 'reason_code' => 'policy_concern', 'explanation' => 'A concise fictional explanation suitable for moderation testing.', 'request_id' => fake()->uuid(), 'safe_metadata' => [], 'submitted_at' => now()];
    }

    public function closed(): static
    {
        return $this->state(fn (): array => ['status' => ReportStatus::Closed, 'closed_at' => now()]);
    }
}
