<?php

namespace Database\Factories;

use App\Enums\NotificationLifecycleStatus;
use App\Enums\NotificationPriority;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserNotification> */
class UserNotificationFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'type' => 'moderation.report.received', 'schema_version' => 1, 'idempotency_key' => fake()->uuid(), 'correlation_key' => fake()->uuid(), 'priority' => NotificationPriority::Normal, 'status' => NotificationLifecycleStatus::Active, 'payload' => ['report_id' => fake()->numberBetween(1, 999999), 'status' => 'submitted']];
    }

    public function read(): static
    {
        return $this->state(fn (): array => ['read_at' => now()]);
    }
}
