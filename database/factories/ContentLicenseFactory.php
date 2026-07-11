<?php

namespace Database\Factories;

use App\Models\ContentLicense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentLicense>
 */
class ContentLicenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->lexify('???? ????');

        return [
            'name' => str($name)->title()->toString(),
            'slug' => str($name)->slug()->toString(),
            'reference_url' => fake()->optional()->url(),
            'attribution_required' => fake()->randomElement([true, false, null]),
            'commercial_use_allowed' => fake()->randomElement([true, false, null]),
            'derivative_use_allowed' => fake()->randomElement([true, false, null]),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
