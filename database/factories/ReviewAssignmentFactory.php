<?php

namespace Database\Factories;

use App\Enums\ReviewAssignmentStatus;
use App\Models\EditorialRevision;
use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewAssignment>
 */
class ReviewAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'editorial_revision_id' => EditorialRevision::factory()->submitted(),
            'reviewer_user_id' => User::factory(),
            'assigned_by_user_id' => User::factory(),
            'status' => ReviewAssignmentStatus::Assigned,
            'is_primary' => true,
            'active_primary_key' => 'primary',
            'assigned_at' => now(),
            'due_at' => now()->addWeek()->toDateString(),
        ];
    }
}
