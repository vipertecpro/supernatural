<?php

namespace Database\Factories;

use App\Enums\CommunityPostStatus;
use App\Enums\CommunityPostVisibility;
use App\Models\CommunityPost;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityPost>
 */
class CommunityPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $body = fake()->paragraph();

        return ['author_user_id' => User::factory(), 'bunker_id' => null, 'universe_id' => Universe::factory(), 'reference_type' => null, 'reference_id' => null, 'title' => fake()->sentence(5), 'body' => $body, 'body_checksum' => hash('sha256', $body), 'status' => CommunityPostStatus::Published, 'visibility' => CommunityPostVisibility::Public, 'comments_enabled' => true, 'lock_version' => 0, 'published_at' => now()];
    }
}
