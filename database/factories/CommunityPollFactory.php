<?php

namespace Database\Factories;

use App\Enums\CommunityPollResultsVisibility;
use App\Enums\CommunityPollStatus;
use App\Enums\CommunityPollType;
use App\Models\CommunityPoll;
use App\Models\CommunityPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityPoll>
 */
class CommunityPollFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['post_id' => CommunityPost::factory(), 'question' => fake()->sentence().'?', 'type' => CommunityPollType::Single, 'maximum_choices' => 1, 'status' => CommunityPollStatus::Open, 'results_visibility' => CommunityPollResultsVisibility::AfterVote, 'lock_version' => 0, 'opens_at' => now(), 'closes_at' => now()->addDay()];
    }
}
