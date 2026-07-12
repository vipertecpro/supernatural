<?php

namespace Database\Factories;

use App\Enums\RestrictionScope;
use App\Models\UserRestriction;
use App\Models\UserRestrictionScope;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserRestrictionScope> */
class UserRestrictionScopeFactory extends Factory
{
    public function definition(): array
    {
        return ['user_restriction_id' => UserRestriction::factory(), 'scope' => RestrictionScope::ReportSubmission];
    }
}
