<?php

namespace Database\Factories;

use App\Enums\PermissionName;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $permission = fake()->randomElement(PermissionName::cases());

        return [
            'name' => $permission,
            'label' => $permission->label(),
            'description' => fake()->sentence(),
        ];
    }
}
