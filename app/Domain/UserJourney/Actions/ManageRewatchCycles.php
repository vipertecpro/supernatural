<?php

namespace App\Domain\UserJourney\Actions;

use App\Domain\UserJourney\Exceptions\InvalidJourneyOperation;
use App\Enums\PersonalVisibility;
use App\Enums\RewatchStatus;
use App\Events\RewatchCycleCompleted;
use App\Events\RewatchCycleStarted;
use App\Models\RewatchCycle;
use App\Models\User;
use App\Models\ViewingOrder;
use App\Models\Work;
use Illuminate\Support\Facades\DB;

class ManageRewatchCycles
{
    public function start(User $user, Work $work, ?ViewingOrder $order = null): RewatchCycle
    {
        if ($order !== null && $order->universe_id !== $work->universe_id) {
            throw new InvalidJourneyOperation('The viewing order must share the rewatch universe.', 'cross_universe_rewatch');
        }

        return DB::transaction(function () use ($user, $work, $order): RewatchCycle {
            User::query()->lockForUpdate()->findOrFail($user->id);
            if (RewatchCycle::query()->where('user_id', $user->id)->where('universe_id', $work->universe_id)->where('work_id', $work->id)->where('active_key', 'active')->exists()) {
                throw new InvalidJourneyOperation('An active rewatch already exists for this work.', 'active_rewatch_exists');
            }
            $cycleNumber = ((int) RewatchCycle::query()->where('user_id', $user->id)->where('work_id', $work->id)->max('cycle_number')) + 1;
            $cycle = RewatchCycle::query()->create(['user_id' => $user->id, 'universe_id' => $work->universe_id, 'work_id' => $work->id, 'viewing_order_id' => $order?->id, 'cycle_number' => $cycleNumber, 'status' => RewatchStatus::Active, 'active_key' => 'active', 'visibility' => PersonalVisibility::Private, 'started_at' => now()]);
            RewatchCycleStarted::dispatch($cycle->id, $user->id, $work->id);

            return $cycle;
        }, attempts: 3);
    }

    public function transition(User $user, RewatchCycle $cycle, RewatchStatus $status): RewatchCycle
    {
        if ($cycle->user_id !== $user->id || $cycle->status !== RewatchStatus::Active || $status === RewatchStatus::Active) {
            throw new InvalidJourneyOperation('The requested rewatch transition is not valid.', 'invalid_rewatch_transition');
        }

        $cycle->update(['status' => $status, 'active_key' => null, 'completed_at' => $status === RewatchStatus::Completed ? now() : null, 'abandoned_at' => $status === RewatchStatus::Abandoned ? now() : null]);
        if ($status === RewatchStatus::Completed) {
            RewatchCycleCompleted::dispatch($cycle->id, $user->id, (int) $cycle->work_id);
        }

        return $cycle->fresh();
    }
}
