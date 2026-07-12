<?php

namespace Database\Factories;

use App\Enums\JourneyStatus;
use App\Enums\PersonalVisibility;
use App\Models\User;
use App\Models\UserViewingJourney;
use App\Models\ViewingOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserViewingJourney>
 */
class UserViewingJourneyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'viewing_order_id' => ViewingOrder::factory()->published(),
            'universe_id' => fn (array $attributes): int => (int) ViewingOrder::query()->whereKey($attributes['viewing_order_id'])->value('universe_id'),
            'status' => JourneyStatus::Active,
            'active_key' => 'active',
            'visibility' => PersonalVisibility::Private,
            'started_at' => now(),
            'lock_version' => 0,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => ['status' => JourneyStatus::Completed, 'active_key' => null, 'completed_at' => now()]);
    }
}
