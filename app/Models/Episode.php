<?php

namespace App\Models;

use App\Concerns\HasEditorialRevisions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\DatePrecision;
use App\Enums\EpisodeType;
use App\Enums\PublicationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\EpisodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property EpisodeType $type
 * @property DatePrecision|null $release_date_precision
 * @property PublicationStatus $status
 * @property CarbonImmutable|null $original_release_date
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $archived_at
 * @property int $lock_version
 */
#[Fillable(['work_id', 'season_id', 'episode_number', 'display_number', 'absolute_number', 'production_code', 'type', 'title', 'slug', 'summary', 'synopsis', 'runtime_minutes', 'original_release_date', 'release_date_precision', 'position', 'status', 'is_public', 'metadata', 'published_at', 'archived_at', 'lock_version', 'created_by', 'updated_by'])]
class Episode extends Model
{
    /** @use HasFactory<EpisodeFactory> */
    use HasEditorialRevisions, HasFactory, HasSpoilerConstraints;

    /** @return BelongsTo<Work, $this> */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /** @return BelongsTo<Season, $this> */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * @param  Builder<Episode>  $query
     * @return Builder<Episode>
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
                    ->where('is_public', true)))
            ->where(fn (Builder $season) => $season
                ->whereNull('season_id')
                ->orWhereHas('season', fn (Builder $item) => $item
                    ->where('status', PublicationStatus::Published)
                    ->where('is_public', true)
                    ->whereNull('archived_at')));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => EpisodeType::class,
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
