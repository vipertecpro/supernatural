<?php

namespace App\Domain\UserJourney\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\UserJourney\Exceptions\InvalidJourneyOperation;
use App\Enums\JourneyStatus;
use App\Enums\PersonalVisibility;
use App\Enums\PublicationStatus;
use App\Events\ViewingJourneyCompleted;
use App\Events\ViewingJourneyStarted;
use App\Models\User;
use App\Models\UserViewingJourney;
use App\Models\ViewingOrder;
use Illuminate\Support\Facades\DB;

class ManageViewingJourneys
{
    public function start(User $user, ViewingOrder $order, ?int $rewatchCycleId = null): UserViewingJourney
    {
        if ($order->status !== PublicationStatus::Published || $order->archived_at !== null || ! ViewingOrder::query()->visibleToPublic()->whereKey($order)->exists()) {
            throw new InvalidJourneyOperation('Only a published active viewing order may be selected.', 'viewing_order_unavailable');
        }

        return DB::transaction(function () use ($user, $order, $rewatchCycleId): UserViewingJourney {
            User::query()->lockForUpdate()->findOrFail($user->id);
            if (UserViewingJourney::query()->where('user_id', $user->id)->where('universe_id', $order->universe_id)->where('active_key', 'active')->exists()) {
                throw new InvalidJourneyOperation('Pause, complete, or abandon the current journey before starting another.', 'active_journey_exists');
            }

            $firstItem = $order->items()->first();
            $journey = UserViewingJourney::query()->create([
                'user_id' => $user->id,
                'universe_id' => $order->universe_id,
                'viewing_order_id' => $order->id,
                'rewatch_cycle_id' => $rewatchCycleId,
                'status' => JourneyStatus::Active,
                'active_key' => 'active',
                'current_item_id' => $firstItem?->id,
                'current_work_id' => $firstItem?->target_type === 'work' ? $firstItem->target_id : null,
                'current_season_id' => $firstItem?->target_type === 'season' ? $firstItem->target_id : null,
                'current_episode_id' => $firstItem?->target_type === 'episode' ? $firstItem->target_id : null,
                'visibility' => PersonalVisibility::Private,
                'started_at' => now(),
                'lock_version' => 0,
            ]);

            ViewingJourneyStarted::dispatch($journey->id, $user->id, $order->id);

            return $journey->fresh(['viewingOrder', 'currentItem']);
        }, attempts: 3);
    }

    public function transition(UserViewingJourney $journey, JourneyStatus $status, int $expectedVersion): UserViewingJourney
    {
        return DB::transaction(function () use ($journey, $status, $expectedVersion): UserViewingJourney {
            $locked = UserViewingJourney::query()->lockForUpdate()->findOrFail($journey->id);
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }

            $allowed = match ($status) {
                JourneyStatus::Paused => $locked->status === JourneyStatus::Active,
                JourneyStatus::Active => $locked->status === JourneyStatus::Paused,
                JourneyStatus::Completed, JourneyStatus::Abandoned => in_array($locked->status, [JourneyStatus::Active, JourneyStatus::Paused], true),
            };
            if (! $allowed) {
                throw new InvalidJourneyOperation('The requested journey transition is not valid.', 'invalid_journey_transition');
            }

            $locked->update([
                'status' => $status,
                'active_key' => in_array($status, [JourneyStatus::Active, JourneyStatus::Paused], true) ? 'active' : null,
                'paused_at' => $status === JourneyStatus::Paused ? now() : ($status === JourneyStatus::Active ? null : $locked->paused_at),
                'completed_at' => $status === JourneyStatus::Completed ? now() : null,
                'abandoned_at' => $status === JourneyStatus::Abandoned ? now() : null,
                'lock_version' => $expectedVersion + 1,
            ]);

            if ($status === JourneyStatus::Completed) {
                ViewingJourneyCompleted::dispatch($locked->id, $locked->user_id, $locked->viewing_order_id);
            }

            return $locked->fresh(['viewingOrder', 'currentItem']);
        }, attempts: 3);
    }

    public function advanceTo(UserViewingJourney $journey, ?int $itemId): UserViewingJourney
    {
        $item = $itemId === null ? null : $journey->viewingOrder->items()->whereKey($itemId)->firstOrFail();
        $journey->update([
            'current_item_id' => $item?->id,
            'current_work_id' => $item?->target_type === 'work' ? $item->target_id : null,
            'current_season_id' => $item?->target_type === 'season' ? $item->target_id : null,
            'current_episode_id' => $item?->target_type === 'episode' ? $item->target_id : null,
        ]);

        return $journey->fresh('currentItem');
    }
}
