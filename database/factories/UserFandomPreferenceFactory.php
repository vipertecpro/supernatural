<?php

namespace Database\Factories;

use App\Enums\PersonalVisibility;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserFandomPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserFandomPreference>
 */
class UserFandomPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'universe_id' => Universe::factory(), 'default_locale' => 'en', 'auto_complete_progress' => false, 'auto_remove_completed_watchlist_items' => false, 'continue_watching_visibility' => PersonalVisibility::Private, 'rating_visibility' => PersonalVisibility::Private, 'favourite_visibility' => PersonalVisibility::Private, 'journey_visibility' => PersonalVisibility::Private, 'lock_version' => 0];
    }
}
