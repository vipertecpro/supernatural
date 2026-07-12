<?php

namespace Database\Factories;

use App\Enums\PersonalVisibility;
use App\Enums\PublicationStatus;
use App\Enums\ViewingOrderType;
use App\Models\Universe;
use App\Models\User;
use App\Models\ViewingOrder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ViewingOrder>
 */
class ViewingOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);

        return [
            'universe_id' => Universe::factory()->published(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 999999),
            'type' => ViewingOrderType::Release,
            'status' => PublicationStatus::Draft,
            'visibility' => PersonalVisibility::Private,
            'is_default' => false,
            'locale' => 'en',
            'created_by' => User::factory(),
            'lock_version' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['status' => PublicationStatus::Published, 'visibility' => PersonalVisibility::Public, 'published_at' => now()]);
    }
}
