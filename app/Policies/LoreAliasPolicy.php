<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\LoreAlias;
use App\Models\User;

class LoreAliasPolicy
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
    public function view(User $user, LoreAlias $loreAlias): bool
    {
        return $user->can('view', $loreAlias->loreEntity);
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
    public function update(User $user, LoreAlias $loreAlias): bool
    {
        return $user->can('update', $loreAlias->loreEntity);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LoreAlias $loreAlias): bool
    {
        return $user->hasPermission(PermissionName::LoreDelete);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LoreAlias $loreAlias): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LoreAlias $loreAlias): bool
    {
        return false;
    }

    public function publish(User $user, LoreAlias $loreAlias): bool
    {
        return $user->hasPermission(PermissionName::LorePublish);
    }

    public function archive(User $user, LoreAlias $loreAlias): bool
    {
        return $user->hasPermission(PermissionName::LoreArchive);
    }
}
