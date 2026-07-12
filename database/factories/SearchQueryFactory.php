<?php

namespace Database\Factories;

use App\Models\SearchQuery;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SearchQuery> */
class SearchQueryFactory extends Factory
{
    public function definition(): array
    {
        return ['universe_id' => null, 'query_hash' => hash('sha256', fake()->uuid()), 'query_length' => 12, 'locale' => 'en', 'document_type' => null, 'result_count_bucket' => 5, 'occurred_at' => now()];
    }
}
