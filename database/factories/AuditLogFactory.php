<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_user_id' => User::factory(),
            'event' => fake()->randomElement(['authorization.role_assigned', 'authorization.role_removed']),
            'auditable_type' => User::class,
            'auditable_id' => User::factory(),
            'metadata' => ['reason' => fake()->sentence()],
            'request_id' => (string) Str::uuid(),
        ];
    }
}
