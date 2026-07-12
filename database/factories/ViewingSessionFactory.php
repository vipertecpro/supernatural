<?php

namespace Database\Factories;

use App\Enums\ProgressSource;
use App\Enums\ViewingSessionStatus;
use App\Models\User;
use App\Models\ViewingSession;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ViewingSession>
 */
class ViewingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'work_id' => Work::factory(), 'status' => ViewingSessionStatus::Active, 'source' => ProgressSource::Manual, 'client_session_id' => (string) Str::uuid(), 'started_at' => now(), 'last_activity_at' => now(), 'starting_position_seconds' => 0, 'ending_position_seconds' => 0, 'watched_seconds' => 0, 'lock_version' => 0];
    }
}
