<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\ContentLicense;
use App\Models\User;

class ContentLicensePolicy
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
    public function view(User $user, ContentLicense $contentLicense): bool
    {
        return $user->hasPermission(PermissionName::ContentContribute)
            || $user->hasPermission(PermissionName::ContentReview);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::ContentReview);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ContentLicense $contentLicense): bool
    {
        return $user->hasPermission(PermissionName::ContentReview);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ContentLicense $contentLicense): bool
    {
        return $user->hasPermission(PermissionName::SettingsManage);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ContentLicense $contentLicense): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ContentLicense $contentLicense): bool
    {
        return false;
    }
}
