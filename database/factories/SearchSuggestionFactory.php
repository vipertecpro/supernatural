<?php

namespace Database\Factories;

use App\Enums\SearchSuggestionType;
use App\Models\SearchDocument;
use App\Models\SearchSuggestion;
use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SearchSuggestion> */
class SearchSuggestionFactory extends Factory
{
    public function definition(): array
    {
        return ['search_document_id' => SearchDocument::factory(), 'universe_id' => Universe::factory(), 'locale' => 'en', 'suggestion_type' => SearchSuggestionType::CanonicalTitle, 'value' => 'The Ember Index', 'normalized_value' => 'the ember index', 'weight' => 100, 'spoiler_sensitive' => false];
    }
}
