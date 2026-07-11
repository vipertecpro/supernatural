<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Work;
use App\Models\WorkTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkTranslation> */
class WorkTranslationFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'work_id' => Work::factory(),
            'locale' => 'fr',
            'title' => 'Les dossiers de braise',
            'short_title' => null,
            'summary' => fake()->sentence(),
            'synopsis' => fake()->paragraph(),
            'translated_from_locale' => 'en',
            'status' => PublicationStatus::Draft,
            'published_at' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function locale(string $locale): static
    {
        return $this->state(fn (): array => ['locale' => str($locale)->replace('_', '-')->lower()->toString()]);
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['status' => PublicationStatus::Published, 'published_at' => now()]);
    }
}
