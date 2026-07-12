<?php

namespace Database\Factories;

use App\Enums\SpoilerTolerance;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserSpoilerPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSpoilerPreference>
 */
class UserSpoilerPreferenceFactory extends Factory
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
            'universe_id' => Universe::factory(),
            'tolerance' => SpoilerTolerance::Strict,
            'show_warnings' => true,
        ];
    }
}
