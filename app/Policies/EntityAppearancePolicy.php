<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\EntityAppearance;
use App\Models\User;

class EntityAppearancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::LoreViewDrafts);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EntityAppearance $entityAppearance): bool
    {
        return $user->can('view', $entityAppearance->loreEntity);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::LoreCreate);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EntityAppearance $entityAppearance): bool
    {
        return $user->can('update', $entityAppearance->loreEntity);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EntityAppearance $entityAppearance): bool
    {
        return $user->hasPermission(PermissionName::LoreDelete);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EntityAppearance $entityAppearance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EntityAppearance $entityAppearance): bool
    {
        return false;
    }

    public function publish(User $user, EntityAppearance $entityAppearance): bool
    {
        return $user->hasPermission(PermissionName::LorePublish);
    }

    public function archive(User $user, EntityAppearance $entityAppearance): bool
    {
        return $user->hasPermission(PermissionName::LoreArchive);
    }
}
