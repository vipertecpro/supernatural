<?php

namespace Database\Factories;

use App\Enums\BunkerMembershipRole;
use App\Enums\BunkerMembershipStatus;
use App\Enums\BunkerStatus;
use App\Enums\BunkerVisibility;
use App\Models\Bunker;
use App\Models\BunkerMembership;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bunker>
 */
class BunkerFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Bunker $bunker): void {
            if ($bunker->owner_user_id === null || $bunker->owner_membership_key !== null) {
                return;
            }
            $membership = BunkerMembership::query()->create(['bunker_id' => $bunker->id, 'user_id' => $bunker->owner_user_id, 'role' => BunkerMembershipRole::Owner, 'status' => BunkerMembershipStatus::Active, 'active_key' => $bunker->id.':'.$bunker->owner_user_id, 'joined_at' => now()]);
            $bunker->update(['owner_membership_key' => $membership->id]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->bothify('Community Group ####');

        return ['universe_id' => Universe::factory(), 'owner_user_id' => User::factory(), 'name' => str($name)->title(), 'slug' => str($name)->slug(), 'description' => fake()->paragraph(), 'rules_summary' => fake()->sentence(), 'visibility' => BunkerVisibility::Public, 'status' => BunkerStatus::Published, 'requires_approval' => false, 'requires_invitation' => false, 'default_locale' => 'en', 'spoiler_severity' => null, 'owner_membership_key' => null, 'lock_version' => 0, 'published_at' => now()];
    }
}
