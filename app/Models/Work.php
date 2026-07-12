<?php

namespace App\Models;

use App\Concerns\HasEditorialRevisions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\CanonStatus;
use App\Enums\DatePrecision;
use App\Enums\PublicationStatus;
use App\Enums\WorkReleaseStatus;
use App\Enums\WorkType;
use Carbon\CarbonImmutable;
use Database\Factories\WorkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property WorkType $type
 * @property WorkReleaseStatus $release_status
 * @property CanonStatus $canon_status
 * @property DatePrecision|null $release_date_precision
 * @property PublicationStatus $status
 * @property CarbonImmutable|null $original_release_date
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $archived_at
 * @property int $lock_version
 */
#[Fillable(['universe_id', 'franchise_id', 'type', 'slug', 'original_title', 'original_language', 'summary', 'runtime_minutes', 'release_status', 'canon_status', 'original_release_date', 'release_date_precision', 'status', 'is_public', 'metadata', 'published_at', 'archived_at', 'lock_version', 'created_by', 'updated_by'])]
class Work extends Model
{
    /** @use HasFactory<WorkFactory> */
    use HasEditorialRevisions, HasFactory, HasSpoilerConstraints;

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return BelongsTo<Franchise, $this> */
    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    /** @return HasMany<WorkTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(WorkTranslation::class);
    }

    /** @return HasOne<SeriesDetail, $this> */
    public function seriesDetail(): HasOne
    {
        return $this->hasOne(SeriesDetail::class);
    }

    /** @return HasMany<Season, $this> */
    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasMany<Episode, $this> */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class)->orderBy('position')->orderBy('id');
    }

    /**
     * @param  Builder<Work>  $query
     * @return Builder<Work>
     */
    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published)
            ->where('is_public', true)
            ->whereNull('archived_at')
            ->whereHas('universe', fn (Builder $universe) => $universe
                ->where('status', PublicationStatus::Published)
                ->where('is_public', true))
            ->where(fn (Builder $franchise) => $franchise
                ->whereNull('franchise_id')
                ->orWhereHas('franchise', fn (Builder $item) => $item
                    ->where('status', PublicationStatus::Published)
                    ->where('is_public', true)
                    ->whereNull('archived_at')));
    }

    /**
     * @param  Builder<Work>  $query
     * @return Builder<Work>
     */
    public function scopeSeries(Builder $query): Builder
    {
        return $query->where('type', WorkType::Series);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => WorkType::class,
            'release_status' => WorkReleaseStatus::class,
            'canon_status' => CanonStatus::class,
            'release_date_precision' => DatePrecision::class,
            'status' => PublicationStatus::class,
            'original_release_date' => 'immutable_date',
            'is_public' => 'boolean',
            'metadata' => 'array',
            'published_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }
}
