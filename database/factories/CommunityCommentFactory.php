<?php

namespace Database\Factories;

use App\Enums\CommunityCommentStatus;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityComment>
 */
class CommunityCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $body = fake()->sentence();

        return ['post_id' => CommunityPost::factory(), 'author_user_id' => User::factory(), 'parent_id' => null, 'root_id' => null, 'depth' => 0, 'body' => $body, 'body_checksum' => hash('sha256', $body), 'status' => CommunityCommentStatus::Published, 'lock_version' => 0];
    }
}
