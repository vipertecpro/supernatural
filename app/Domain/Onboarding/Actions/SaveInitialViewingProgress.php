<?php

namespace App\Domain\Onboarding\Actions;

use App\Domain\UserJourney\Actions\RecordViewingProgress;
use App\Enums\OnboardingStep;
use App\Enums\ProgressSource;
use App\Enums\ProgressStatus;
use App\Models\Episode;
use App\Models\User;
use App\Models\UserFandomPreference;
use App\Models\UserOnboardingState;
use App\Models\ViewingProgress;
use App\Models\Work;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SaveInitialViewingProgress
{
    private const MAX_EPISODES_THROUGH = 100;

    public function __construct(
        private readonly AdvanceOnboarding $advance,
        private readonly RecordViewingProgress $progress,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function handle(User $user, UserOnboardingState $state, array $attributes): UserOnboardingState
    {
        $mode = (string) $attributes['mode'];

        return $this->advance->handle(
            $state,
            OnboardingStep::ViewingProgress,
            (int) $attributes['expected_version'],
            function () use ($user, $state, $attributes, $mode): void {
                if ($mode === 'skip') {
                    return;
                }

                $work = Work::query()->visibleToPublic()->findOrFail((int) $attributes['work_id']);
                $isInterested = UserFandomPreference::query()
                    ->where('user_id', $user->id)
                    ->where('universe_id', $work->universe_id)
                    ->exists();
                if (! $isInterested) {
                    throw ValidationException::withMessages(['work_id' => 'Choose a work from one of your selected universes.']);
                }

                if ($mode === 'watched_through') {
                    $this->markEpisodesThrough($user, $state, $work, (int) $attributes['episode_id']);

                    return;
                }

                $status = $mode === 'completed_work' ? ProgressStatus::Completed : ProgressStatus::NotStarted;
                $current = ViewingProgress::query()
                    ->where('user_id', $user->id)
                    ->where('cycle_key', 0)
                    ->where('scope_key', 'work:'.$work->id)
                    ->first();

                $this->progress->handle($user, 'work', $work->id, [
                    'status' => $status->value,
                    'source' => ProgressSource::Manual->value,
                    'expected_version' => $current->lock_version ?? 0,
                    'client_request_id' => "onboarding-{$state->id}-work-{$work->id}-{$mode}",
                ]);
            },
        );
    }

    private function markEpisodesThrough(User $user, UserOnboardingState $state, Work $work, int $episodeId): void
    {
        /** @var Collection<int, Episode> $episodes */
        $episodes = Episode::query()
            ->visibleToPublic()
            ->where('work_id', $work->id)
            ->with('season:id,position')
            ->limit(self::MAX_EPISODES_THROUGH + 1)
            ->get()
            ->sortBy(fn (Episode $episode): string => sprintf('%08d:%08d:%08d', $episode->season->position ?? 0, $episode->position, $episode->id))
            ->values();

        $boundaryIndex = $episodes->search(fn (Episode $episode): bool => $episode->id === $episodeId);
        if ($boundaryIndex === false || $boundaryIndex >= self::MAX_EPISODES_THROUGH) {
            throw ValidationException::withMessages(['episode_id' => 'Choose an available episode within the bounded onboarding list.']);
        }

        foreach ($episodes->take($boundaryIndex + 1) as $episode) {
            $current = ViewingProgress::query()
                ->where('user_id', $user->id)
                ->where('cycle_key', 0)
                ->where('scope_key', 'episode:'.$episode->id)
                ->first();
            $this->progress->handle($user, 'episode', $episode->id, [
                'status' => ProgressStatus::Completed->value,
                'source' => ProgressSource::Manual->value,
                'expected_version' => $current->lock_version ?? 0,
                'client_request_id' => "onboarding-{$state->id}-episode-{$episode->id}-complete",
            ]);
        }
    }
}
