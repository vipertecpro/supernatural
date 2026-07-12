<?php

namespace Database\Factories;

use App\Models\EditorialRevision;
use App\Models\RevisionBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RevisionBlock>
 */
class RevisionBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'editorial_revision_id' => EditorialRevision::factory(),
            'field' => 'summary',
            'locale' => null,
            'original_text_checksum' => hash('sha256', 'null'),
            'proposed_text' => 'An original editorial summary about a fictional signal expedition.',
            'format' => 'plain_text',
            'position' => 0,
            'source_required' => false,
            'rights_required' => false,
        ];
    }
}
