<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\PublicationStatus;
use App\Models\Source;
use App\Models\User;

class SourcePolicy
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
    public function view(User $user, Source $source): bool
    {
        return ($source->universe?->status === PublicationStatus::Published && $source->universe->is_public)
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
    public function update(User $user, Source $source): bool
    {
        return $user->hasPermission(PermissionName::ContentReview);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Source $source): bool
    {
        return $user->hasPermission(PermissionName::SettingsManage);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Source $source): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Source $source): bool
    {
        return false;
    }
}
