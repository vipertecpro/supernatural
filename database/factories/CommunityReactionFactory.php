<?php

namespace Database\Factories;

use App\Enums\CommunityReactionType;
use App\Models\CommunityPost;
use App\Models\CommunityReaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityReaction>
 */
class CommunityReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'reactable_type' => 'community_post', 'reactable_id' => CommunityPost::factory(), 'type' => CommunityReactionType::Like];
    }
}
