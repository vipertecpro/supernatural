<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\TimelineEntry;
use App\Models\User;

class TimelineEntryPolicy
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
    public function view(User $user, TimelineEntry $timelineEntry): bool
    {
        return $user->can('view', $timelineEntry->timeline);
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
    public function update(User $user, TimelineEntry $timelineEntry): bool
    {
        return $user->can('update', $timelineEntry->timeline);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TimelineEntry $timelineEntry): bool
    {
        return $user->hasPermission(PermissionName::LoreDelete);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TimelineEntry $timelineEntry): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TimelineEntry $timelineEntry): bool
    {
        return false;
    }

    public function publish(User $user, TimelineEntry $timelineEntry): bool
    {
        return $user->hasPermission(PermissionName::LoreTimelinesPublish);
    }

    public function archive(User $user, TimelineEntry $timelineEntry): bool
    {
        return $user->hasPermission(PermissionName::LoreArchive);
    }
}
