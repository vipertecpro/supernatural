<?php

namespace App\Domain\Identity\Actions;

use App\Enums\UserMuteScope;
use App\Events\UserBlocked;
use App\Events\UserMuted;
use App\Events\UserUnblocked;
use App\Events\UserUnmuted;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserMute;
use App\Support\AuditLogger;
use Illuminate\Validation\ValidationException;

class ManageInteractionSafety
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function block(User $actor, User $target, ?string $reasonCode = null): UserBlock
    {
        $this->assertDifferentUsers($actor, $target);
        $block = UserBlock::query()->firstOrCreate(
            ['blocker_user_id' => $actor->id, 'blocked_user_id' => $target->id],
            ['reason_code' => $reasonCode],
        );
        if ($block->wasRecentlyCreated) {
            $this->audit->record('identity.user_blocked', $block, ['target_user_id' => $target->id], $actor);
            UserBlocked::dispatch($block->id, $actor->id, $target->id);
        }

        return $block;
    }

    public function unblock(UserBlock $block, User $actor): void
    {
        if ($block->blocker_user_id !== $actor->id) {
            abort(404);
        }
        $data = [$block->id, $block->blocker_user_id, $block->blocked_user_id];
        $this->audit->record('identity.user_unblocked', $block, ['target_user_id' => $block->blocked_user_id], $actor);
        $block->delete();
        UserUnblocked::dispatch(...$data);
    }

    public function mute(User $actor, User $target, UserMuteScope $scope, ?string $expiresAt): UserMute
    {
        $this->assertDifferentUsers($actor, $target);
        $mute = UserMute::query()->firstOrCreate(
            ['muting_user_id' => $actor->id, 'muted_user_id' => $target->id, 'scope' => $scope],
            ['expires_at' => $expiresAt],
        );
        if (! $mute->wasRecentlyCreated) {
            $mute->update(['expires_at' => $expiresAt]);
        }
        if ($mute->wasRecentlyCreated) {
            $this->audit->record('identity.user_muted', $mute, ['target_user_id' => $target->id, 'scope' => $scope->value], $actor);
            UserMuted::dispatch($mute->id, $actor->id, $target->id, $scope->value);
        }

        return $mute;
    }

    public function unmute(UserMute $mute, User $actor): void
    {
        if ($mute->muting_user_id !== $actor->id) {
            abort(404);
        }
        $data = [$mute->id, $mute->muting_user_id, $mute->muted_user_id, $mute->scope->value];
        $this->audit->record('identity.user_unmuted', $mute, ['target_user_id' => $mute->muted_user_id, 'scope' => $mute->scope->value], $actor);
        $mute->delete();
        UserUnmuted::dispatch(...$data);
    }

    private function assertDifferentUsers(User $actor, User $target): void
    {
        if ($actor->is($target)) {
            throw ValidationException::withMessages(['target_user_id' => ['You cannot apply this interaction preference to yourself.']]);
        }
    }
}
