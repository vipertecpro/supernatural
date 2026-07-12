<?php

namespace Database\Factories;

use App\Models\Citation;
use App\Models\CitationSource;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CitationSource>
 */
class CitationSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'citation_id' => Citation::factory(),
            'source_id' => Source::factory(),
            'relationship' => 'supports',
            'position' => 0,
        ];
    }
}
