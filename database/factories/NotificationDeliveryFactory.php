<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Models\NotificationDelivery;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NotificationDelivery> */
class NotificationDeliveryFactory extends Factory
{
    public function definition(): array
    {
        return ['notification_id' => UserNotification::factory(), 'channel' => NotificationChannel::InApp, 'status' => NotificationDeliveryStatus::Delivered, 'attempt_number' => 1, 'scheduled_at' => now(), 'attempted_at' => now(), 'delivered_at' => now()];
    }

    public function failed(): static
    {
        return $this->state(fn (): array => ['status' => NotificationDeliveryStatus::Failed, 'failed_at' => now(), 'failure_code' => 'transport_failed', 'retry_at' => now()->addMinutes(5)]);
    }
}
