<?php

namespace Database\Factories;

use App\Models\CommunityPost;
use App\Models\CommunityTag;
use App\Models\CommunityTaggable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityTaggable>
 */
class CommunityTaggableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['tag_id' => CommunityTag::factory(), 'taggable_type' => 'community_post', 'taggable_id' => CommunityPost::factory()];
    }
}
