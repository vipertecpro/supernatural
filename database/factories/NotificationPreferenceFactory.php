<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPreferenceState;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NotificationPreference> */
class NotificationPreferenceFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'type' => 'journey.completed', 'channel' => NotificationChannel::Email, 'state' => NotificationPreferenceState::Enabled, 'lock_version' => 0];
    }
}
