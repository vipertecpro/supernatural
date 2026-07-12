<?php

namespace Database\Seeders;

use App\Models\BunkerCategory;
use Illuminate\Database\Seeder;

class BunkerCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['key' => 'general', 'name' => 'General Discussion', 'description' => 'Broad community discussion.', 'position' => 10],
            ['key' => 'analysis', 'name' => 'Analysis', 'description' => 'Thoughtful analysis and interpretation.', 'position' => 20],
            ['key' => 'creative', 'name' => 'Creative Work', 'description' => 'Original community-created work.', 'position' => 30],
            ['key' => 'help', 'name' => 'Help and Questions', 'description' => 'Questions and practical help.', 'position' => 40],
        ] as $definition) {
            BunkerCategory::query()->updateOrCreate(['key' => $definition['key']], $definition + ['is_active' => true]);
        }
    }
}
