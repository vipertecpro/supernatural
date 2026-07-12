<?php

namespace App\Policies;

use App\Enums\MediaStatus;
use App\Enums\PermissionName;
use App\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::MediaViewDrafts);
    }

    public function view(?User $user, MediaAsset $asset): bool
    {
        return MediaAsset::query()->visibleToPublic()->whereKey($asset)->exists() || ($user?->hasPermission(PermissionName::MediaViewDrafts) === true);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::MediaCreate);
    }

    public function update(User $user, MediaAsset $asset): bool
    {
        return $user->hasPermission(PermissionName::MediaModerate) || ($user->hasPermission(PermissionName::MediaUpdateOwnDrafts) && $asset->owner_user_id === $user->id && $asset->status === MediaStatus::Pending);
    }

    public function publish(User $user): bool
    {
        return $user->hasPermission(PermissionName::MediaPublish);
    }

    public function archive(User $user): bool
    {
        return $user->hasPermission(PermissionName::MediaArchive);
    }

    public function delete(User $user, MediaAsset $asset): bool
    {
        return $user->hasPermission(PermissionName::MediaModerate) && ! $asset->attachments()->exists();
    }
}
