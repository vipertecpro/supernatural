<?php

namespace Database\Factories;

use App\Models\CommunityBookmark;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityBookmark>
 */
class CommunityBookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'bookmarkable_type' => 'community_post', 'bookmarkable_id' => CommunityPost::factory()];
    }
}
