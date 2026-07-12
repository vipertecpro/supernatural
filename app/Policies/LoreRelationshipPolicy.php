<?php

namespace App\Policies;

use App\Enums\LoreRelationshipStatus;
use App\Enums\PermissionName;
use App\Models\LoreRelationship;
use App\Models\User;

class LoreRelationshipPolicy
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
    public function view(User $user, LoreRelationship $loreRelationship): bool
    {
        return $loreRelationship->status === LoreRelationshipStatus::Published || $user->hasPermission(PermissionName::LoreViewDrafts);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::LoreRelationshipsCreate);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LoreRelationship $loreRelationship): bool
    {
        return $user->hasPermission(PermissionName::LoreRelationshipsReview) || ($loreRelationship->created_by === $user->id && $loreRelationship->status === LoreRelationshipStatus::Draft);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LoreRelationship $loreRelationship): bool
    {
        return $user->hasPermission(PermissionName::LoreDelete);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LoreRelationship $loreRelationship): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LoreRelationship $loreRelationship): bool
    {
        return false;
    }

    public function publish(User $user, LoreRelationship $loreRelationship): bool
    {
        return $user->hasPermission(PermissionName::LoreRelationshipsPublish);
    }

    public function archive(User $user, LoreRelationship $loreRelationship): bool
    {
        return $user->hasPermission(PermissionName::LoreArchive);
    }
}
