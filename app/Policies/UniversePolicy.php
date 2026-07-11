<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\PublicationStatus;
use App\Models\Universe;
use App\Models\User;

class UniversePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::ContentContribute)
            || $user->hasPermission(PermissionName::ContentReview);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Universe $universe): bool
    {
        return ($universe->status === PublicationStatus::Published && $universe->is_public)
            || $user->hasPermission(PermissionName::ContentContribute)
            || $user->hasPermission(PermissionName::ContentReview);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::ContentContribute);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Universe $universe): bool
    {
        return $user->hasPermission(PermissionName::ContentReview);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Universe $universe): bool
    {
        return $user->hasPermission(PermissionName::SettingsManage);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Universe $universe): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Universe $universe): bool
    {
        return false;
    }
}
