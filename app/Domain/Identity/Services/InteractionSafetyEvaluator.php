<?php

namespace App\Domain\Identity\Services;

use App\Enums\UserMuteScope;
use App\Models\Bunker;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserMute;

class InteractionSafetyEvaluator
{
    public function hasBlocked(User|int $actor, User|int $target): bool
    {
        [$actorId, $targetId] = [$this->id($actor), $this->id($target)];

        return $actorId !== $targetId && UserBlock::query()->where(['blocker_user_id' => $actorId, 'blocked_user_id' => $targetId])->exists();
    }

    public function hasEitherBlocked(User|int $first, User|int $second): bool
    {
        [$firstId, $secondId] = [$this->id($first), $this->id($second)];
        if ($firstId === $secondId) {
            return false;
        }

        return UserBlock::query()->where(fn ($query) => $query
            ->where(['blocker_user_id' => $firstId, 'blocked_user_id' => $secondId])
            ->orWhere(['blocker_user_id' => $secondId, 'blocked_user_id' => $firstId]))->exists();
    }

    public function hasMuted(User|int $viewer, User|int $author, ?UserMuteScope $scope = null): bool
    {
        [$viewerId, $authorId] = [$this->id($viewer), $this->id($author)];
        if ($viewerId === $authorId) {
            return false;
        }

        return UserMute::query()->active()->where(['muting_user_id' => $viewerId, 'muted_user_id' => $authorId])
            ->when($scope !== null, fn ($query) => $query->whereIn('scope', [UserMuteScope::All->value, $scope->value]))->exists();
    }

    public function mayInitiateDirectInteraction(User|int $actor, User|int $target): bool
    {
        return ! $this->hasEitherBlocked($actor, $target);
    }

    public function mayMention(User|int $actor, User|int $target): bool
    {
        return $this->mayInitiateDirectInteraction($actor, $target);
    }

    public function mayInviteToBunker(User|int $actor, User|int $target, Bunker|int $bunker): bool
    {
        return $this->mayInitiateDirectInteraction($actor, $target);
    }

    public function shouldSuppressAuthoredContent(User|int $viewer, User|int $author): bool
    {
        return $this->hasEitherBlocked($viewer, $author) || $this->hasMuted($viewer, $author, UserMuteScope::CommunityContent);
    }

    public function shouldSuppressOptionalNotification(User|int $recipient, User|int $actor, string $notificationType): bool
    {
        if ($this->hasEitherBlocked($recipient, $actor)) {
            return true;
        }

        $scope = str_contains($notificationType, 'bunker.invited') ? UserMuteScope::BunkerInvitations : UserMuteScope::Mentions;

        return $this->hasMuted($recipient, $actor, $scope);
    }

    private function id(User|int $user): int
    {
        return $user instanceof User ? $user->id : $user;
    }
}
