<?php

namespace Database\Factories;

use App\Enums\UserMuteScope;
use App\Models\User;
use App\Models\UserMute;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserMute> */
class UserMuteFactory extends Factory
{
    public function definition(): array
    {
        return ['muting_user_id' => User::factory(), 'muted_user_id' => User::factory(), 'scope' => UserMuteScope::All, 'expires_at' => null];
    }

    public function expired(): static
    {
        return $this->state(fn (): array => ['expires_at' => now()->subMinute()]);
    }
}
