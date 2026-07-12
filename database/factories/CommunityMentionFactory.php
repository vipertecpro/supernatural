<?php

namespace Database\Factories;

use App\Enums\CommunityMentionType;
use App\Models\CommunityMention;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityMention>
 */
class CommunityMentionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['mentionable_type' => 'community_post', 'mentionable_id' => CommunityPost::factory(), 'mentioned_user_id' => User::factory(), 'mentioning_user_id' => User::factory(), 'type' => CommunityMentionType::Post, 'notification_key' => fake()->uuid(), 'inactive_at' => null];
    }
}
