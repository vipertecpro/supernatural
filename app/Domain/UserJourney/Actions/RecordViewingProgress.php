<?php

namespace App\Domain\UserJourney\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\UserJourney\Exceptions\InvalidJourneyOperation;
use App\Domain\UserJourney\Services\JourneyTargetRegistry;
use App\Enums\ProgressEventType;
use App\Enums\ProgressSource;
use App\Enums\ProgressStatus;
use App\Events\EpisodeCompleted;
use App\Events\ViewingProgressUpdated;
use App\Events\WorkCompleted;
use App\Models\Episode;
use App\Models\RewatchCycle;
use App\Models\User;
use App\Models\UserViewingJourney;
use App\Models\ViewingProgress;
use App\Models\ViewingProgressEvent;
use App\Models\Work;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RecordViewingProgress
{
    public function __construct(
        private readonly JourneyTargetRegistry $targets,
        private readonly ManageViewingJourneys $journeys,
        private readonly AuditLogger $auditLogger,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function handle(User $user, string $type, int $id, array $attributes): ViewingProgress
    {
        $requestId = isset($attributes['client_request_id']) ? (string) $attributes['client_request_id'] : null;
        if ($requestId !== null) {
            $prior = ViewingProgressEvent::query()->where('user_id', $user->id)->where('client_request_id', $requestId)->first();
            if ($prior !== null) {
                return $prior->progress()->firstOrFail();
            }
        }

        $path = $this->targets->progressPath($type, $id);
        $journey = $this->ownedJourney($user, $attributes['journey_id'] ?? null, $path['universe_id']);
        $rewatch = $this->ownedRewatch($user, $attributes['rewatch_cycle_id'] ?? null, $path['universe_id']);
        $source = ProgressSource::from((string) ($attributes['source'] ?? ProgressSource::Manual->value));
        $requestedStatus = ProgressStatus::from((string) ($attributes['status'] ?? ProgressStatus::InProgress->value));

        return DB::transaction(function () use ($user, $type, $path, $journey, $rewatch, $source, $requestedStatus, $attributes, $requestId): ViewingProgress {
            $cycleKey = $rewatch === null ? 0 : $rewatch->id;
            $progress = ViewingProgress::query()
                ->where('user_id', $user->id)
                ->where('cycle_key', $cycleKey)
                ->where('scope_key', $path['scope_key'])
                ->lockForUpdate()
                ->first();

            $expectedVersion = (int) ($attributes['expected_version'] ?? 0);
            if ($progress !== null && $progress->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            if ($progress === null && $expectedVersion !== 0) {
                throw new OptimisticLockConflict;
            }

            $previousStatus = $progress?->status;
            $previousPosition = $progress?->runtime_position_seconds;
            $previousBasisPoints = $progress === null ? 0 : $progress->progress_basis_points;
            $position = array_key_exists('runtime_position_seconds', $attributes) ? (int) $attributes['runtime_position_seconds'] : $previousPosition;
            $basisPoints = $this->basisPoints($path['target'], $requestedStatus, $position, $attributes, $previousBasisPoints);
            $isCorrection = (bool) ($attributes['allow_backward'] ?? false);

            if (! $isCorrection && ($basisPoints < $previousBasisPoints || ($position !== null && $previousPosition !== null && $position < $previousPosition))) {
                throw new InvalidJourneyOperation('Progress cannot move backwards without an explicit correction.', 'progress_moved_backwards');
            }

            $values = [
                'user_id' => $user->id,
                'user_viewing_journey_id' => $journey?->id,
                'rewatch_cycle_id' => $rewatch?->id,
                'scope_type' => $type,
                'scope_key' => $path['scope_key'],
                'cycle_key' => $cycleKey,
                'universe_id' => $path['universe_id'],
                'work_id' => $path['work_id'],
                'season_id' => $path['season_id'],
                'episode_id' => $path['episode_id'],
                'status' => $requestedStatus,
                'progress_basis_points' => $basisPoints,
                'runtime_position_seconds' => $position,
                'started_at' => $progress === null ? now() : $progress->started_at,
                'last_watched_at' => now(),
                'completed_at' => $requestedStatus === ProgressStatus::Completed ? ($progress === null || $progress->completed_at === null ? now() : $progress->completed_at) : null,
                'source' => $source,
                'is_manual_override' => $isCorrection || $source === ProgressSource::Manual,
                'is_legacy_projection' => false,
                'last_request_id' => $requestId,
                'lock_version' => $expectedVersion + 1,
            ];

            if ($progress === null) {
                $progress = ViewingProgress::query()->create($values);
            } else {
                $progress->update($values);
            }

            ViewingProgressEvent::query()->create([
                'user_id' => $user->id,
                'viewing_progress_id' => $progress->id,
                'user_viewing_journey_id' => $journey?->id,
                'rewatch_cycle_id' => $rewatch?->id,
                'event_type' => $this->eventType($previousStatus, $requestedStatus, $isCorrection),
                'previous_status' => $previousStatus,
                'new_status' => $requestedStatus,
                'previous_position_seconds' => $previousPosition,
                'new_position_seconds' => $position,
                'client_request_id' => $requestId,
                'source' => $source,
                'safe_metadata' => ['scope_type' => $type],
                'occurred_at' => now(),
            ]);

            if ($isCorrection) {
                $this->auditLogger->record('journey.progress_manually_corrected', $progress, ['scope_type' => $type, 'from_version' => $expectedVersion, 'to_version' => $expectedVersion + 1], $user);
            }

            if ($type === 'episode') {
                $this->recalculateParents($user, $progress, $rewatch?->id);
            }
            if ($journey !== null && $requestedStatus === ProgressStatus::Completed) {
                $this->advanceJourney($journey, $type, (int) $path['target']->getKey());
            }

            ViewingProgressUpdated::dispatch($progress->id, $user->id, $requestedStatus->value);
            if ($requestedStatus === ProgressStatus::Completed && $type === 'episode') {
                EpisodeCompleted::dispatch((int) $path['episode_id'], $user->id, $rewatch?->id);
            }
            if ($requestedStatus === ProgressStatus::Completed && $type === 'work') {
                WorkCompleted::dispatch((int) $path['work_id'], $user->id, $rewatch?->id);
            }

            return $progress->fresh(['work', 'season', 'episode']);
        }, attempts: 3);
    }

    public function reset(User $user, ViewingProgress $progress, int $expectedVersion, bool $resetSpoilerKnowledge = false): ViewingProgress
    {
        return DB::transaction(function () use ($user, $progress, $expectedVersion, $resetSpoilerKnowledge): ViewingProgress {
            $locked = ViewingProgress::query()->lockForUpdate()->findOrFail($progress->id);
            if ($locked->user_id !== $user->id) {
                throw new InvalidJourneyOperation('The progress record does not belong to this user.', 'progress_not_owned');
            }
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }

            $locked->update(['status' => ProgressStatus::NotStarted, 'progress_basis_points' => 0, 'runtime_position_seconds' => null, 'completed_at' => null, 'last_watched_at' => now(), 'is_manual_override' => true, 'lock_version' => $expectedVersion + 1]);
            ViewingProgressEvent::query()->create(['user_id' => $user->id, 'viewing_progress_id' => $locked->id, 'user_viewing_journey_id' => $locked->user_viewing_journey_id, 'rewatch_cycle_id' => $locked->rewatch_cycle_id, 'event_type' => ProgressEventType::Reset, 'previous_status' => $progress->status, 'new_status' => ProgressStatus::NotStarted, 'source' => ProgressSource::Manual, 'safe_metadata' => ['spoiler_knowledge_reset' => $resetSpoilerKnowledge], 'occurred_at' => now()]);
            $this->auditLogger->record('journey.progress_reset', $locked, ['spoiler_knowledge_reset' => $resetSpoilerKnowledge, 'version' => $locked->lock_version], $user);

            return $locked->fresh();
        }, attempts: 3);
    }

    /** @param array<string, mixed> $attributes */
    private function basisPoints(Model $target, ProgressStatus $status, ?int $position, array $attributes, int $previous): int
    {
        if ($status === ProgressStatus::Completed) {
            return 10000;
        }

        $runtimeSeconds = match (true) {
            $target instanceof Episode => $target->runtime_minutes === null ? null : $target->runtime_minutes * 60,
            $target instanceof Work => $target->runtime_minutes === null ? null : $target->runtime_minutes * 60,
            default => null,
        };
        if ($position !== null && $position < 0) {
            throw new InvalidJourneyOperation('Runtime position cannot be negative.', 'invalid_progress_position');
        }
        if ($runtimeSeconds !== null && $position !== null && $position > $runtimeSeconds + 120) {
            throw new InvalidJourneyOperation('Runtime position exceeds the known runtime tolerance.', 'invalid_progress_position');
        }

        if ($runtimeSeconds !== null && $position !== null) {
            return min(9999, (int) floor(($position / max(1, $runtimeSeconds)) * 10000));
        }

        $basisPoints = array_key_exists('progress_basis_points', $attributes) ? (int) $attributes['progress_basis_points'] : $previous;
        if ($basisPoints < 0 || $basisPoints > 10000) {
            throw new InvalidJourneyOperation('Progress basis points must be between 0 and 10000.', 'invalid_progress_percentage');
        }

        return $basisPoints;
    }

    private function eventType(?ProgressStatus $previous, ProgressStatus $next, bool $isCorrection): ProgressEventType
    {
        if ($isCorrection) {
            return ProgressEventType::ManuallyCorrected;
        }
        if ($next === ProgressStatus::Completed) {
            return ProgressEventType::MarkedComplete;
        }
        if ($previous === ProgressStatus::Completed) {
            return ProgressEventType::MarkedIncomplete;
        }

        return $previous === null ? ProgressEventType::Started : ProgressEventType::PositionUpdated;
    }

    private function ownedJourney(User $user, mixed $journeyId, int $universeId): ?UserViewingJourney
    {
        if ($journeyId === null) {
            return null;
        }

        return UserViewingJourney::query()->where('user_id', $user->id)->where('universe_id', $universeId)->findOrFail((int) $journeyId);
    }

    private function ownedRewatch(User $user, mixed $rewatchId, int $universeId): ?RewatchCycle
    {
        if ($rewatchId === null) {
            return null;
        }

        return RewatchCycle::query()->where('user_id', $user->id)->where('universe_id', $universeId)->findOrFail((int) $rewatchId);
    }

    private function recalculateParents(User $user, ViewingProgress $episodeProgress, ?int $rewatchId): void
    {
        $cycleKey = $rewatchId ?? 0;
        $season = $episodeProgress->season;
        if ($season === null) {
            return;
        }

        $totalEpisodes = Episode::query()->where('season_id', $season->id)->count();
        $completedEpisodes = ViewingProgress::query()->where('user_id', $user->id)->where('cycle_key', $cycleKey)->where('season_id', $season->id)->where('scope_type', 'episode')->where('status', ProgressStatus::Completed)->count();
        $this->upsertDerived($user, 'season', $season->id, $season->work_id, $season->id, null, $season->work->universe_id, $cycleKey, $rewatchId, $completedEpisodes, $totalEpisodes);

        $totalWorkEpisodes = Episode::query()->where('work_id', $season->work_id)->count();
        $completedWorkEpisodes = ViewingProgress::query()->where('user_id', $user->id)->where('cycle_key', $cycleKey)->where('work_id', $season->work_id)->where('scope_type', 'episode')->where('status', ProgressStatus::Completed)->count();
        $workProgress = $this->upsertDerived($user, 'work', $season->work_id, $season->work_id, null, null, $season->work->universe_id, $cycleKey, $rewatchId, $completedWorkEpisodes, $totalWorkEpisodes);
        if ($workProgress->status === ProgressStatus::Completed) {
            WorkCompleted::dispatch($season->work_id, $user->id, $rewatchId);
        }
    }

    private function upsertDerived(User $user, string $type, int $id, int $workId, ?int $seasonId, ?int $episodeId, int $universeId, int $cycleKey, ?int $rewatchId, int $completed, int $total): ViewingProgress
    {
        $progress = ViewingProgress::query()->where('user_id', $user->id)->where('cycle_key', $cycleKey)->where('scope_key', $type.':'.$id)->first();
        if ($progress?->is_manual_override === true) {
            return $progress;
        }

        $basisPoints = $total === 0 ? 0 : (int) floor(($completed / $total) * 10000);
        $status = $total > 0 && $completed >= $total ? ProgressStatus::Completed : ($completed > 0 ? ProgressStatus::InProgress : ProgressStatus::NotStarted);

        return ViewingProgress::query()->updateOrCreate(
            ['user_id' => $user->id, 'cycle_key' => $cycleKey, 'scope_key' => $type.':'.$id],
            ['rewatch_cycle_id' => $rewatchId, 'scope_type' => $type, 'universe_id' => $universeId, 'work_id' => $workId, 'season_id' => $seasonId, 'episode_id' => $episodeId, 'status' => $status, 'progress_basis_points' => $basisPoints, 'started_at' => now(), 'last_watched_at' => now(), 'completed_at' => $status === ProgressStatus::Completed ? now() : null, 'source' => ProgressSource::Session, 'is_manual_override' => false, 'is_legacy_projection' => false, 'lock_version' => ($progress === null ? 0 : $progress->lock_version + 1)],
        );
    }

    private function advanceJourney(UserViewingJourney $journey, string $type, int $targetId): void
    {
        $current = $journey->viewingOrder->items()->where('target_type', $type)->where('target_id', $targetId)->first();
        if ($current === null) {
            return;
        }
        $next = $journey->viewingOrder->items()->where('position', '>', $current->position)->orderBy('position')->first();
        $this->journeys->advanceTo($journey, $next?->id);
    }
}
