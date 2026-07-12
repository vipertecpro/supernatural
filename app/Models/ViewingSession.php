<?php

namespace App\Models;

use App\Enums\ProgressSource;
use App\Enums\ViewingSessionStatus;
use Database\Factories\ViewingSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $user_viewing_journey_id
 * @property int|null $rewatch_cycle_id
 * @property int $work_id
 * @property int|null $season_id
 * @property int|null $episode_id
 * @property ViewingSessionStatus $status
 * @property ProgressSource $source
 * @property string $client_session_id
 * @property mixed $started_at
 * @property mixed $last_activity_at
 * @property mixed $ended_at
 * @property int $starting_position_seconds
 * @property int $ending_position_seconds
 * @property int $watched_seconds
 * @property array<string, mixed>|null $safe_metadata
 * @property int $lock_version
 */
#[Fillable(['user_id', 'user_viewing_journey_id', 'rewatch_cycle_id', 'work_id', 'season_id', 'episode_id', 'status', 'source', 'client_session_id', 'started_at', 'last_activity_at', 'ended_at', 'starting_position_seconds', 'ending_position_seconds', 'watched_seconds', 'safe_metadata', 'lock_version'])]
class ViewingSession extends Model
{
    /** @use HasFactory<ViewingSessionFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => ViewingSessionStatus::class, 'source' => ProgressSource::class, 'started_at' => 'immutable_datetime', 'last_activity_at' => 'immutable_datetime', 'ended_at' => 'immutable_datetime', 'safe_metadata' => 'array', 'lock_version' => 'integer'];
    }
}
