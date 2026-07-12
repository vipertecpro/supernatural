<?php

namespace App\Models;

use App\Concerns\HasEditorialRevisions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\DatePrecision;
use App\Enums\PublicationStatus;
use App\Enums\SeasonType;
use Carbon\CarbonImmutable;
use Database\Factories\SeasonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property SeasonType $type
 * @property DatePrecision|null $release_date_precision
 * @property PublicationStatus $status
 * @property CarbonImmutable|null $original_release_date
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $archived_at
 * @property int $lock_version
 */
#[Fillable(['work_id', 'type', 'number', 'display_number', 'title', 'slug', 'summary', 'position', 'original_release_date', 'release_date_precision', 'status', 'is_public', 'metadata', 'published_at', 'archived_at', 'lock_version', 'created_by', 'updated_by'])]
class Season extends Model
{
    /** @use HasFactory<SeasonFactory> */
    use HasEditorialRevisions, HasFactory, HasSpoilerConstraints;

    /** @return BelongsTo<Work, $this> */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /** @return HasMany<Episode, $this> */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class)->orderBy('position')->orderBy('id');
    }

    /**
     * @param  Builder<Season>  $query
     * @return Builder<Season>
     */
    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published)
            ->where('is_public', true)
            ->whereNull('archived_at')
            ->whereHas('work', fn (Builder $work) => $work
                ->where('status', PublicationStatus::Published)
                ->where('is_public', true)
                ->whereNull('archived_at')
                ->whereHas('universe', fn (Builder $universe) => $universe
                    ->where('status', PublicationStatus::Published)
                    ->where('is_public', true)));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => SeasonType::class,
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
