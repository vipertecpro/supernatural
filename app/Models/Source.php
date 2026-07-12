<?php

namespace App\Models;

use App\Concerns\HasSpoilerConstraints;
use App\Enums\SourceType;
use Database\Factories\SourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property SourceType $source_type */
#[Fillable(['universe_id', 'content_license_id', 'title', 'canonical_url', 'source_type', 'publisher', 'author', 'published_at', 'accessed_at', 'attribution_text', 'usage_notes', 'metadata'])]
class Source extends Model
{
    /** @use HasFactory<SourceFactory> */
    use HasFactory, HasSpoilerConstraints;

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return BelongsTo<ContentLicense, $this> */
    public function contentLicense(): BelongsTo
    {
        return $this->belongsTo(ContentLicense::class);
    }

    /** @return HasMany<SourceRightsReview, $this> */
    public function rightsReviews(): HasMany
    {
        return $this->hasMany(SourceRightsReview::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'source_type' => SourceType::class,
            'published_at' => 'date',
            'accessed_at' => 'date',
            'metadata' => 'array',
        ];
    }
}
