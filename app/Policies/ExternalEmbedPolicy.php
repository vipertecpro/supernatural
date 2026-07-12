<?php

namespace App\Policies;

use App\Domain\Moderation\Services\RestrictionEvaluator;
use App\Enums\MediaStatus;
use App\Enums\PermissionName;
use App\Models\ExternalEmbed;
use App\Models\User;

class ExternalEmbedPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::MediaViewDrafts);
    }

    public function view(?User $user, ExternalEmbed $embed): bool
    {
        return ExternalEmbed::query()->visibleToPublic()->whereKey($embed)->exists() || ($user?->hasPermission(PermissionName::MediaViewDrafts) === true);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::MediaCreate);
    }

    public function update(User $user, ExternalEmbed $embed): bool
    {
        return ! app(RestrictionEvaluator::class)->isEditingFrozen($embed) && ($user->hasPermission(PermissionName::MediaModerate) || ($user->hasPermission(PermissionName::MediaUpdateOwnDrafts) && $embed->owner_user_id === $user->id && $embed->status === MediaStatus::Pending));
    }

    public function publish(User $user, ExternalEmbed $embed): bool
    {
        return ! app(RestrictionEvaluator::class)->isEditingFrozen($embed) && $user->hasPermission(PermissionName::MediaPublish);
    }

    public function archive(User $user, ExternalEmbed $embed): bool
    {
        return ! app(RestrictionEvaluator::class)->isEditingFrozen($embed) && $user->hasPermission(PermissionName::MediaArchive);
    }
}
