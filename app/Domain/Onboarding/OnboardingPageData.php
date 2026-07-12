<?php

namespace App\Domain\Onboarding;

use App\Enums\OnboardingStep;
use App\Enums\PublicationStatus;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserFandomPreference;
use App\Models\UserOnboardingState;
use App\Models\UserSpoilerPreference;
use App\Models\ViewingOrder;
use App\Models\ViewingProgress;
use App\Models\Work;

class OnboardingPageData
{
    /** @return array<string, mixed> */
    public function shared(UserOnboardingState $state, OnboardingStep $pageStep): array
    {
        return [
            'currentStep' => $pageStep->value,
            'resumeStep' => $state->current_step->value,
            'version' => $state->lock_version,
            'startedAt' => $state->started_at?->toAtomString(),
            'steps' => array_map(fn (OnboardingStep $step): array => [
                'key' => $step->value,
                'label' => $step->label(),
                'href' => $step->position() <= $state->current_step->position() ? route($step->routeName()) : null,
                'status' => match (true) {
                    $step === $pageStep => 'current',
                    $step->position() < $state->current_step->position() => 'complete',
                    default => 'upcoming',
                },
            ], OnboardingStep::workflow()),
            'backHref' => $pageStep->previous() === null ? null : route($pageStep->previous()->routeName()),
        ];
    }

    /** @return list<array{id:int,name:string,description:?string,workCount:int,selected:bool}> */
    public function universes(User $user): array
    {
        $selected = UserFandomPreference::query()->where('user_id', $user->id)->pluck('universe_id');
        $universes = Universe::query()
            ->where('status', PublicationStatus::Published)
            ->where('is_public', true)
            ->orderBy('name')
            ->limit(25)
            ->get();
        $workCounts = Work::query()
            ->visibleToPublic()
            ->whereIn('universe_id', $universes->pluck('id'))
            ->selectRaw('universe_id, count(*) as aggregate')
            ->groupBy('universe_id')
            ->pluck('aggregate', 'universe_id');

        $result = [];
        foreach ($universes as $universe) {
            $result[] = [
                'id' => $universe->id,
                'name' => $universe->name,
                'description' => $universe->description,
                'workCount' => (int) ($workCounts[$universe->id] ?? 0),
                'selected' => $selected->contains($universe->id),
            ];
        }

        return $result;
    }

    /** @return list<array<string, mixed>> */
    public function progressCatalog(User $user): array
    {
        $universeIds = UserFandomPreference::query()->where('user_id', $user->id)->pluck('universe_id');
        if ($universeIds->isEmpty()) {
            return [];
        }

        $works = Work::query()
            ->visibleToPublic()
            ->whereIn('universe_id', $universeIds)
            ->with([
                'universe:id,name',
                'seasons' => fn ($query) => $query->visibleToPublic()->with([
                    'episodes' => fn ($episodes) => $episodes->visibleToPublic()->orderBy('position')->limit(100),
                ])->orderBy('position')->limit(25),
                'episodes' => fn ($query) => $query->visibleToPublic()->whereNull('season_id')->orderBy('position')->limit(100),
            ])
            ->orderBy('original_title')
            ->limit(100)
            ->get();

        $result = [];
        foreach ($works as $work) {
            $seasons = [];
            foreach ($work->seasons as $season) {
                $episodes = [];
                foreach ($season->episodes as $episode) {
                    $episodes[] = [
                        'id' => $episode->id,
                        'label' => 'Episode '.($episode->display_number ?: $episode->episode_number),
                    ];
                }
                $seasons[] = [
                    'id' => $season->id,
                    'label' => $season->display_number ?: 'Season '.$season->number,
                    'episodes' => $episodes,
                ];
            }

            $standaloneEpisodes = [];
            foreach ($work->episodes as $episode) {
                $standaloneEpisodes[] = [
                    'id' => $episode->id,
                    'label' => 'Episode '.($episode->display_number ?: $episode->episode_number),
                ];
            }

            $result[] = [
                'id' => $work->id,
                'universeId' => $work->universe_id,
                'universeName' => $work->universe->name,
                'title' => $work->original_title,
                'seasons' => $seasons,
                'standaloneEpisodes' => $standaloneEpisodes,
            ];
        }

        return $result;
    }

    /** @return list<array<string, mixed>> */
    public function viewingOrders(User $user): array
    {
        $universeIds = UserFandomPreference::query()->where('user_id', $user->id)->pluck('universe_id');

        $orders = ViewingOrder::query()
            ->visibleToPublic()
            ->whereIn('universe_id', $universeIds)
            ->with('universe:id,name')
            ->withCount('items')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->limit(100)
            ->get();

        $result = [];
        foreach ($orders as $order) {
            $result[] = [
                'id' => $order->id,
                'universeId' => $order->universe_id,
                'universeName' => $order->universe->name,
                'name' => $order->name,
                'description' => $order->description,
                'type' => $order->type->value,
                'locale' => $order->locale,
                'itemCount' => $order->items_count,
                'isDefault' => $order->is_default,
            ];
        }

        return $result;
    }

    /** @return array<string, mixed> */
    public function review(User $user): array
    {
        $preferences = UserFandomPreference::query()
            ->where('user_id', $user->id)
            ->with(['universe:id,name', 'preferredViewingOrder:id,name'])
            ->orderBy('universe_id')
            ->get();
        $spoilers = UserSpoilerPreference::query()->where('user_id', $user->id)->get()->keyBy('universe_id');
        $progress = ViewingProgress::query()
            ->where('user_id', $user->id)
            ->with('work:id,original_title')
            ->latest('last_watched_at')
            ->first();

        return [
            'universes' => $preferences->map(fn (UserFandomPreference $preference): array => [
                'id' => $preference->universe_id,
                'name' => $preference->universe->name,
            ])->all(),
            'progress' => $progress === null ? null : [
                'work' => $progress->work->original_title,
                'status' => $progress->status->value,
            ],
            'spoilers' => $preferences->map(function (UserFandomPreference $preference) use ($spoilers): array {
                $spoiler = $spoilers->get($preference->universe_id);

                return [
                    'universe' => $preference->universe->name,
                    'tolerance' => $spoiler === null ? 'strict' : $spoiler->tolerance->value,
                    'warnings' => $spoiler === null ? true : $spoiler->show_warnings,
                ];
            })->all(),
            'viewingOrders' => $preferences->filter(fn (UserFandomPreference $preference): bool => $preference->preferredViewingOrder !== null)
                ->map(fn (UserFandomPreference $preference): array => [
                    'universe' => $preference->universe->name,
                    'name' => $preference->preferredViewingOrder->name,
                ])->values()->all(),
            'privacy' => [
                'Viewing progress and Journey are private',
                'Favourites and ratings are private',
                'Watchlists and personal notes remain private by domain policy',
                'Blocks and mutes are always private',
            ],
        ];
    }
}
