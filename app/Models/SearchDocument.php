<?php

namespace App\Models;

use App\Enums\SearchDocumentType;
use App\Enums\SearchProjectionStatus;
use Database\Factories\SearchDocumentFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $source_type
 * @property int $source_id
 * @property int $universe_id
 * @property string $locale
 * @property SearchDocumentType $document_type
 * @property string $canonical_title
 * @property string|null $localized_title
 * @property string|null $searchable_summary
 * @property string $normalized_text
 * @property string $slug
 * @property string $route_key
 * @property SearchProjectionStatus $status
 * @property string $visibility
 * @property string|null $canon_classification
 * @property string|null $spoiler_severity
 * @property int $ranking_weight
 * @property int $source_lock_version
 * @property Collection<int, SearchSuggestion> $suggestions
 */
class SearchDocument extends Model
{
    /** @use HasFactory<SearchDocumentFactory> */
    use HasFactory;

    protected $fillable = ['source_type', 'source_id', 'universe_id', 'locale', 'document_type', 'canonical_title', 'localized_title', 'searchable_summary', 'normalized_text', 'slug', 'route_key', 'status', 'visibility', 'canon_classification', 'spoiler_severity', 'spoiler_boundary', 'ranking_weight', 'popularity_score', 'projection_version', 'source_lock_version', 'facets', 'safe_metadata', 'freshness_at', 'indexed_at', 'archived_at'];

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return HasMany<SearchSuggestion, $this> */
    public function suggestions(): HasMany
    {
        return $this->hasMany(SearchSuggestion::class);
    }

    protected function casts(): array
    {
        return ['document_type' => SearchDocumentType::class, 'status' => SearchProjectionStatus::class, 'spoiler_boundary' => 'array', 'facets' => 'array', 'safe_metadata' => 'array', 'freshness_at' => 'immutable_datetime', 'indexed_at' => 'immutable_datetime', 'archived_at' => 'immutable_datetime'];
    }
}
