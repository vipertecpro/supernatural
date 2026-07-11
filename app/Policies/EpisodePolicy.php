<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\PublicationStatus;
use App\Models\Episode;
use App\Models\User;

class EpisodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::CatalogViewDrafts);
    }

    public function view(User $user, Episode $episode): bool
    {
        return $episode->status === PublicationStatus::Published && $episode->is_public && $episode->archived_at === null || $user->hasPermission(PermissionName::CatalogViewDrafts);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::CatalogCreate);
    }

    public function update(User $user, Episode $episode): bool
    {
        return $user->hasPermission(PermissionName::CatalogUpdate) && ($user->hasPermission(PermissionName::CatalogPublish) || ($episode->created_by === $user->id && $episode->status === PublicationStatus::Draft));
    }

    public function publish(User $user, Episode $episode): bool
    {
        return $user->hasPermission(PermissionName::CatalogPublish);
    }

    public function archive(User $user, Episode $episode): bool
    {
        return $user->hasPermission(PermissionName::CatalogArchive);
    }

    public function delete(User $user, Episode $episode): bool
    {
        return $user->hasPermission(PermissionName::CatalogDelete);
    }

    public function restore(User $user, Episode $episode): bool
    {
        return false;
    }

    public function forceDelete(User $user, Episode $episode): bool
    {
        return false;
    }
}
