<?php

namespace App\Models;

use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use Database\Factories\SpoilerConstraintFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $universe_id
 * @property SpoilerSeverity $severity
 * @property SpoilerClassificationStatus $classification_status
 * @property string|null $warning
 */
#[Fillable(['universe_id', 'spoilerable_type', 'spoilerable_id', 'severity', 'classification_status', 'earliest_progress', 'warning', 'classified_by', 'reviewed_by', 'classified_at', 'reviewed_at', 'metadata'])]
class SpoilerConstraint extends Model
{
    /** @use HasFactory<SpoilerConstraintFactory> */
    use HasFactory;

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return MorphTo<Model, $this> */
    public function spoilerable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<SpoilerBoundary, $this> */
    public function boundaries(): HasMany
    {
        return $this->hasMany(SpoilerBoundary::class);
    }

    /** @return HasMany<SpoilerCorrection, $this> */
    public function corrections(): HasMany
    {
        return $this->hasMany(SpoilerCorrection::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'severity' => SpoilerSeverity::class,
            'classification_status' => SpoilerClassificationStatus::class,
            'earliest_progress' => 'array',
            'metadata' => 'array',
            'classified_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
        ];
    }
}
