<?php

namespace App\Models;

use App\Enums\CanonClassification;
use App\Enums\CitationEvidenceStrength;
use App\Enums\CitationReviewStatus;
use Database\Factories\CitationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $citable_type
 * @property int $citable_id
 * @property CitationEvidenceStrength $evidence_strength
 * @property CanonClassification $canon_classification
 * @property CitationReviewStatus $review_status
 * @property int $added_by_user_id
 */
#[Fillable(['citable_type', 'citable_id', 'field_key', 'locator', 'page_number', 'timecode', 'chapter', 'section', 'quotation_excerpt', 'note', 'evidence_strength', 'canon_classification', 'added_by_user_id', 'review_status'])]
class Citation extends Model
{
    /** @use HasFactory<CitationFactory> */
    use HasFactory;

    /** @return MorphTo<Model, $this> */
    public function citable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<CitationSource, $this> */
    public function citationSources(): HasMany
    {
        return $this->hasMany(CitationSource::class);
    }

    /** @return BelongsTo<User, $this> */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'evidence_strength' => CitationEvidenceStrength::class,
            'canon_classification' => CanonClassification::class,
            'review_status' => CitationReviewStatus::class,
        ];
    }
}
