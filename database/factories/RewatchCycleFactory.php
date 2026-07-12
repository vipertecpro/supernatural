<?php

namespace Database\Factories;

use App\Enums\PersonalVisibility;
use App\Enums\RewatchStatus;
use App\Models\RewatchCycle;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RewatchCycle>
 */
class RewatchCycleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'work_id' => Work::factory(),
            'universe_id' => fn (array $attributes): int => (int) Work::query()->whereKey($attributes['work_id'])->value('universe_id'),
            'cycle_number' => 1,
            'status' => RewatchStatus::Active,
            'active_key' => 'active',
            'visibility' => PersonalVisibility::Private,
            'started_at' => now(),
        ];
    }
}
