<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\PublicationStatus;
use App\Models\Franchise;
use App\Models\User;

class FranchisePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::CatalogViewDrafts);
    }

    public function view(User $user, Franchise $franchise): bool
    {
        return $franchise->status === PublicationStatus::Published && $franchise->is_public && $franchise->archived_at === null
            || $user->hasPermission(PermissionName::CatalogViewDrafts);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::CatalogCreate);
    }

    public function update(User $user, Franchise $franchise): bool
    {
        return $user->hasPermission(PermissionName::CatalogUpdate)
            && ($user->hasPermission(PermissionName::CatalogPublish)
                || ($franchise->created_by === $user->id && $franchise->status === PublicationStatus::Draft));
    }

    public function publish(User $user, Franchise $franchise): bool
    {
        return $user->hasPermission(PermissionName::CatalogPublish);
    }

    public function archive(User $user, Franchise $franchise): bool
    {
        return $user->hasPermission(PermissionName::CatalogArchive);
    }

    public function delete(User $user, Franchise $franchise): bool
    {
        return $user->hasPermission(PermissionName::CatalogDelete);
    }

    public function restore(User $user, Franchise $franchise): bool
    {
        return false;
    }

    public function forceDelete(User $user, Franchise $franchise): bool
    {
        return false;
    }
}
