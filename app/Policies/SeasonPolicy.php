<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\PublicationStatus;
use App\Models\Season;
use App\Models\User;

class SeasonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::CatalogViewDrafts);
    }

    public function view(User $user, Season $season): bool
    {
        return $season->status === PublicationStatus::Published && $season->is_public && $season->archived_at === null || $user->hasPermission(PermissionName::CatalogViewDrafts);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::CatalogCreate);
    }

    public function update(User $user, Season $season): bool
    {
        return $user->hasPermission(PermissionName::CatalogUpdate) && ($user->hasPermission(PermissionName::CatalogPublish) || ($season->created_by === $user->id && $season->status === PublicationStatus::Draft));
    }

    public function publish(User $user, Season $season): bool
    {
        return $user->hasPermission(PermissionName::CatalogPublish);
    }

    public function archive(User $user, Season $season): bool
    {
        return $user->hasPermission(PermissionName::CatalogArchive);
    }

    public function delete(User $user, Season $season): bool
    {
        return $user->hasPermission(PermissionName::CatalogDelete);
    }

    public function restore(User $user, Season $season): bool
    {
        return false;
    }

    public function forceDelete(User $user, Season $season): bool
    {
        return false;
    }
}
