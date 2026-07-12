<?php

namespace App\Domain\Moderation\Services;

use App\Enums\ContentRestrictionType;
use App\Enums\RestrictionScope;
use App\Models\ContentRestriction;
use App\Models\User;
use App\Models\UserRestriction;
use Illuminate\Database\Eloquent\Model;

class RestrictionEvaluator
{
    public function hasUserScope(User|int $user, RestrictionScope $scope): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return UserRestriction::query()->currentlyActive()
            ->where('user_id', $userId)
            ->whereHas('scopes', fn ($query) => $query->where('scope', $scope->value))
            ->exists();
    }

    /** @param list<ContentRestrictionType>|null $types */
    public function hasContentRestriction(Model $target, ?array $types = null): bool
    {
        $query = ContentRestriction::query()->currentlyActive()
            ->where('target_type', $target->getMorphClass())
            ->where('target_id', $target->getKey());

        if ($types !== null) {
            $query->whereIn('type', array_map(fn (ContentRestrictionType $type): string => $type->value, $types));
        }

        return $query->exists();
    }

    public function isHiddenFromPublic(Model $target): bool
    {
        return $this->hasContentRestriction($target, [ContentRestrictionType::HiddenFromPublic, ContentRestrictionType::TakedownRestricted]);
    }

    public function isHiddenFromSearch(Model $target): bool
    {
        return $this->hasContentRestriction($target, [ContentRestrictionType::HiddenFromPublic, ContentRestrictionType::HiddenFromSearch, ContentRestrictionType::TakedownRestricted]);
    }

    public function isEditingFrozen(Model $target): bool
    {
        return $this->hasContentRestriction($target, [ContentRestrictionType::EditingFrozen, ContentRestrictionType::TakedownRestricted]);
    }

    public function areAttachmentsBlocked(Model $target): bool
    {
        return $this->hasContentRestriction($target, [ContentRestrictionType::AttachmentsBlocked, ContentRestrictionType::TakedownRestricted]);
    }
}
