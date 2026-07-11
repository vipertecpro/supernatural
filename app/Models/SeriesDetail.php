<?php

namespace App\Models;

use App\Enums\EpisodeOrder;
use App\Enums\SeriesFormat;
use App\Enums\SeriesStatus;
use Carbon\CarbonImmutable;
use Database\Factories\SeriesDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property SeriesFormat $format
 * @property SeriesStatus $series_status
 * @property EpisodeOrder $default_episode_order
 * @property CarbonImmutable|null $premiere_date
 * @property CarbonImmutable|null $end_date
 */
#[Fillable(['work_id', 'format', 'series_status', 'premiere_date', 'end_date', 'default_episode_duration', 'default_episode_order', 'metadata'])]
class SeriesDetail extends Model
{
    /** @use HasFactory<SeriesDetailFactory> */
    use HasFactory;

    /** @return BelongsTo<Work, $this> */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'format' => SeriesFormat::class,
            'series_status' => SeriesStatus::class,
            'premiere_date' => 'immutable_date',
            'end_date' => 'immutable_date',
            'default_episode_order' => EpisodeOrder::class,
            'metadata' => 'array',
        ];
    }
}
