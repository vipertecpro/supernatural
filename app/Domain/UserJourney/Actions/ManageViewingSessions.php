<?php

namespace App\Domain\UserJourney\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\UserJourney\Exceptions\InvalidJourneyOperation;
use App\Domain\UserJourney\Services\JourneyTargetRegistry;
use App\Enums\ProgressSource;
use App\Enums\ProgressStatus;
use App\Enums\ViewingSessionStatus;
use App\Models\User;
use App\Models\ViewingProgress;
use App\Models\ViewingSession;
use Illuminate\Support\Facades\DB;

class ManageViewingSessions
{
    public function __construct(
        private readonly JourneyTargetRegistry $targets,
        private readonly RecordViewingProgress $progress,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function start(User $user, array $attributes): ViewingSession
    {
        $existing = ViewingSession::query()->where('user_id', $user->id)->where('client_session_id', $attributes['client_session_id'])->first();
        if ($existing !== null) {
            return $existing;
        }

        $path = $this->targets->progressPath((string) $attributes['target_type'], (int) $attributes['target_id']);

        return ViewingSession::query()->create([
            'user_id' => $user->id,
            'user_viewing_journey_id' => $attributes['journey_id'] ?? null,
            'rewatch_cycle_id' => $attributes['rewatch_cycle_id'] ?? null,
            'work_id' => $path['work_id'],
            'season_id' => $path['season_id'],
            'episode_id' => $path['episode_id'],
            'status' => ViewingSessionStatus::Active,
            'source' => ProgressSource::from((string) ($attributes['source'] ?? 'session')),
            'client_session_id' => $attributes['client_session_id'],
            'started_at' => now(),
            'last_activity_at' => now(),
            'starting_position_seconds' => (int) ($attributes['position_seconds'] ?? 0),
            'ending_position_seconds' => (int) ($attributes['position_seconds'] ?? 0),
            'watched_seconds' => 0,
            'safe_metadata' => isset($attributes['safe_metadata']) ? array_intersect_key((array) $attributes['safe_metadata'], array_flip(['client_platform', 'app_version'])) : null,
            'lock_version' => 0,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    public function update(User $user, ViewingSession $session, array $attributes, bool $end = false): ViewingSession
    {
        return DB::transaction(function () use ($user, $session, $attributes, $end): ViewingSession {
            $locked = ViewingSession::query()->lockForUpdate()->findOrFail($session->id);
            if ($locked->user_id !== $user->id) {
                throw new InvalidJourneyOperation('The viewing session does not belong to this user.', 'viewing_session_not_owned');
            }
            if ($locked->status === ViewingSessionStatus::Ended) {
                return $locked;
            }
            $expectedVersion = (int) ($attributes['expected_version'] ?? 0);
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }

            $position = (int) ($attributes['position_seconds'] ?? $locked->ending_position_seconds);
            if ($position < 0) {
                throw new InvalidJourneyOperation('Session positions cannot be negative.', 'invalid_session_position');
            }
            $elapsed = min(max(0, now()->diffInSeconds($locked->last_activity_at)), 900);
            $watched = min($locked->watched_seconds + $elapsed, 86400);
            $locked->update(['ending_position_seconds' => $position, 'watched_seconds' => $watched, 'last_activity_at' => now(), 'status' => $end ? ViewingSessionStatus::Ended : ViewingSessionStatus::Active, 'ended_at' => $end ? now() : null, 'lock_version' => $expectedVersion + 1]);

            if ((bool) ($attributes['update_progress'] ?? true)) {
                $type = $locked->episode_id !== null ? 'episode' : ($locked->season_id !== null ? 'season' : 'work');
                $targetId = $locked->{$type.'_id'};
                $current = ViewingProgress::query()->where('user_id', $user->id)->where('cycle_key', $locked->rewatch_cycle_id ?? 0)->where('scope_key', $type.':'.$targetId)->first();
                $this->progress->handle($user, $type, (int) $targetId, ['journey_id' => $locked->user_viewing_journey_id, 'rewatch_cycle_id' => $locked->rewatch_cycle_id, 'runtime_position_seconds' => $position, 'status' => (string) ($attributes['progress_status'] ?? ProgressStatus::InProgress->value), 'source' => ProgressSource::Session->value, 'expected_version' => $current === null ? 0 : $current->lock_version, 'client_request_id' => $attributes['client_request_id'] ?? null]);
            }

            return $locked->fresh();
        }, attempts: 3);
    }
}
