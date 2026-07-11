<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\PublicationStatus;
use App\Models\User;
use App\Models\Work;

class WorkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::CatalogViewDrafts);
    }

    public function view(User $user, Work $work): bool
    {
        return $work->status === PublicationStatus::Published && $work->is_public && $work->archived_at === null || $user->hasPermission(PermissionName::CatalogViewDrafts);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::CatalogCreate);
    }

    public function update(User $user, Work $work): bool
    {
        return $user->hasPermission(PermissionName::CatalogUpdate) && ($user->hasPermission(PermissionName::CatalogPublish) || ($work->created_by === $user->id && $work->status === PublicationStatus::Draft));
    }

    public function publish(User $user, Work $work): bool
    {
        return $user->hasPermission(PermissionName::CatalogPublish);
    }

    public function archive(User $user, Work $work): bool
    {
        return $user->hasPermission(PermissionName::CatalogArchive);
    }

    public function delete(User $user, Work $work): bool
    {
        return $user->hasPermission(PermissionName::CatalogDelete);
    }

    public function restore(User $user, Work $work): bool
    {
        return false;
    }

    public function forceDelete(User $user, Work $work): bool
    {
        return false;
    }
}
