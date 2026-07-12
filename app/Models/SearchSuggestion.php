<?php

namespace App\Models;

use App\Enums\SearchSuggestionType;
use Database\Factories\SearchSuggestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $search_document_id
 * @property int $universe_id
 * @property string $locale
 * @property SearchSuggestionType $suggestion_type
 * @property string $value
 * @property string $normalized_value
 * @property int $weight
 * @property bool $spoiler_sensitive
 * @property SearchDocument $searchDocument
 */
class SearchSuggestion extends Model
{
    /** @use HasFactory<SearchSuggestionFactory> */
    use HasFactory;

    protected $fillable = ['search_document_id', 'universe_id', 'locale', 'suggestion_type', 'value', 'normalized_value', 'weight', 'spoiler_sensitive'];

    /** @return BelongsTo<SearchDocument, $this> */
    public function searchDocument(): BelongsTo
    {
        return $this->belongsTo(SearchDocument::class);
    }

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    protected function casts(): array
    {
        return ['suggestion_type' => SearchSuggestionType::class, 'spoiler_sensitive' => 'boolean'];
    }
}
