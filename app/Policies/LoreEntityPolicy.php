<?php

namespace App\Policies;

use App\Domain\Moderation\Services\RestrictionEvaluator;
use App\Enums\LoreVisibility;
use App\Enums\PermissionName;
use App\Enums\PublicationStatus;
use App\Models\LoreEntity;
use App\Models\User;

class LoreEntityPolicy
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
    public function view(User $user, LoreEntity $loreEntity): bool
    {
        return $loreEntity->status === PublicationStatus::Published && $loreEntity->visibility === LoreVisibility::Public && $loreEntity->archived_at === null || $user->hasPermission(PermissionName::LoreViewDrafts);
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
    public function update(User $user, LoreEntity $loreEntity): bool
    {
        return ! app(RestrictionEvaluator::class)->isEditingFrozen($loreEntity) && $user->hasPermission(PermissionName::LoreUpdate) && ($user->hasPermission(PermissionName::LorePublish) || ($loreEntity->created_by === $user->id && $loreEntity->status === PublicationStatus::Draft));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LoreEntity $loreEntity): bool
    {
        return $user->hasPermission(PermissionName::LoreDelete);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LoreEntity $loreEntity): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LoreEntity $loreEntity): bool
    {
        return false;
    }

    public function publish(User $user, LoreEntity $loreEntity): bool
    {
        return ! app(RestrictionEvaluator::class)->isEditingFrozen($loreEntity) && $user->hasPermission(PermissionName::LorePublish);
    }

    public function archive(User $user, LoreEntity $loreEntity): bool
    {
        return ! app(RestrictionEvaluator::class)->isEditingFrozen($loreEntity) && $user->hasPermission(PermissionName::LoreArchive);
    }
}
