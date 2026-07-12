<?php

namespace Database\Factories;

use App\Models\CommunityPollOption;
use App\Models\CommunityPollVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityPollVote>
 */
class CommunityPollVoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['poll_option_id' => CommunityPollOption::factory(), 'poll_id' => fn (array $attributes): int => CommunityPollOption::query()->findOrFail((int) $attributes['poll_option_id'])->poll_id, 'user_id' => User::factory()];
    }
}
