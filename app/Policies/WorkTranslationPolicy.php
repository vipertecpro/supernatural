<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\User;
use App\Models\WorkTranslation;

class WorkTranslationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::CatalogViewDrafts);
    }

    public function view(User $user, WorkTranslation $translation): bool
    {
        return $user->can('view', $translation->work);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::CatalogCreate);
    }

    public function update(User $user, WorkTranslation $translation): bool
    {
        return $user->can('update', $translation->work);
    }

    public function publish(User $user, WorkTranslation $translation): bool
    {
        return $user->hasPermission(PermissionName::CatalogPublish);
    }

    public function delete(User $user, WorkTranslation $translation): bool
    {
        return $user->hasPermission(PermissionName::CatalogDelete);
    }

    public function restore(User $user, WorkTranslation $translation): bool
    {
        return false;
    }

    public function forceDelete(User $user, WorkTranslation $translation): bool
    {
        return false;
    }
}
