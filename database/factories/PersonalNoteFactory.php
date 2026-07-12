<?php

namespace Database\Factories;

use App\Enums\PersonalVisibility;
use App\Models\PersonalNote;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonalNote>
 */
class PersonalNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'target_type' => 'work', 'target_id' => Work::factory(), 'universe_id' => fn (array $attributes): int => (int) Work::query()->whereKey($attributes['target_id'])->value('universe_id'), 'title' => fake()->sentence(3), 'body' => fake()->paragraph(), 'format' => 'plain_text', 'visibility' => PersonalVisibility::Private, 'is_pinned' => false, 'is_spoiler_sensitive' => false, 'lock_version' => 0];
    }
}
