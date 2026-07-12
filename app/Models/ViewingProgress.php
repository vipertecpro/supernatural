<?php

namespace App\Models;

use App\Enums\ProgressSource;
use App\Enums\ProgressStatus;
use Database\Factories\ViewingProgressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $user_viewing_journey_id
 * @property int|null $rewatch_cycle_id
 * @property string $scope_type
 * @property string $scope_key
 * @property int $cycle_key
 * @property int $universe_id
 * @property int $work_id
 * @property int|null $season_id
 * @property int|null $episode_id
 * @property ProgressStatus $status
 * @property int $progress_basis_points
 * @property int|null $runtime_position_seconds
 * @property mixed $started_at
 * @property mixed $last_watched_at
 * @property mixed $completed_at
 * @property ProgressSource $source
 * @property bool $is_manual_override
 * @property bool $is_legacy_projection
 * @property string|null $last_request_id
 * @property int $lock_version
 */
#[Fillable(['user_id', 'user_viewing_journey_id', 'rewatch_cycle_id', 'scope_type', 'scope_key', 'cycle_key', 'universe_id', 'work_id', 'season_id', 'episode_id', 'status', 'progress_basis_points', 'runtime_position_seconds', 'started_at', 'last_watched_at', 'completed_at', 'source', 'is_manual_override', 'is_legacy_projection', 'last_request_id', 'lock_version'])]
class ViewingProgress extends Model
{
    /** @use HasFactory<ViewingProgressFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

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

    /** @return BelongsTo<Episode, $this> */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /** @return BelongsTo<UserViewingJourney, $this> */
    public function journey(): BelongsTo
    {
        return $this->belongsTo(UserViewingJourney::class, 'user_viewing_journey_id');
    }

    /** @return BelongsTo<RewatchCycle, $this> */
    public function rewatchCycle(): BelongsTo
    {
        return $this->belongsTo(RewatchCycle::class);
    }

    /** @return HasMany<ViewingProgressEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ViewingProgressEvent::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => ProgressStatus::class,
            'source' => ProgressSource::class,
            'started_at' => 'immutable_datetime',
            'last_watched_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'is_manual_override' => 'boolean',
            'is_legacy_projection' => 'boolean',
            'progress_basis_points' => 'integer',
            'runtime_position_seconds' => 'integer',
            'lock_version' => 'integer',
        ];
    }
}
