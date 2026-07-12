<?php

namespace App\Enums;

enum SearchSuggestionType: string
{
    case CanonicalTitle = 'canonical_title';
    case LocalizedTitle = 'localized_title';
    case Alias = 'alias';
    case Slug = 'slug';
}
