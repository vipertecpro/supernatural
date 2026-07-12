<?php

namespace App\Policies;

use App\Enums\LoreVisibility;
use App\Enums\PermissionName;
use App\Enums\PublicationStatus;
use App\Models\Timeline;
use App\Models\User;

class TimelinePolicy
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
    public function view(User $user, Timeline $timeline): bool
    {
        return $timeline->status === PublicationStatus::Published && $timeline->visibility === LoreVisibility::Public && $timeline->archived_at === null || $user->hasPermission(PermissionName::LoreViewDrafts);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::LoreTimelinesCreate);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Timeline $timeline): bool
    {
        return $user->hasPermission(PermissionName::LoreTimelinesUpdate) && ($user->hasPermission(PermissionName::LoreTimelinesPublish) || ($timeline->created_by === $user->id && $timeline->status === PublicationStatus::Draft));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Timeline $timeline): bool
    {
        return $user->hasPermission(PermissionName::LoreDelete);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Timeline $timeline): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Timeline $timeline): bool
    {
        return false;
    }

    public function publish(User $user, Timeline $timeline): bool
    {
        return $user->hasPermission(PermissionName::LoreTimelinesPublish);
    }

    public function archive(User $user, Timeline $timeline): bool
    {
        return $user->hasPermission(PermissionName::LoreArchive);
    }
}
