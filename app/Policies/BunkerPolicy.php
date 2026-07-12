<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Bunker;
use App\Models\BunkerMembership;
use App\Models\User;

class BunkerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bunker $bunker): bool
    {
        return $bunker->visibility->value === 'public' || $this->hasRole($user, $bunker, ['owner', 'administrator', 'moderator', 'member']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail() && $user->hasPermission(PermissionName::CommunityBunkersCreate);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bunker $bunker): bool
    {
        return $this->hasRole($user, $bunker, ['owner', 'administrator']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bunker $bunker): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Bunker $bunker): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Bunker $bunker): bool
    {
        return false;
    }

    public function moderate(User $user, Bunker $bunker): bool
    {
        return $this->hasRole($user, $bunker, ['owner', 'administrator', 'moderator']);
    }

    public function transferOwnership(User $user, Bunker $bunker): bool
    {
        return $this->hasRole($user, $bunker, ['owner']);
    }

    /** @param list<string> $roles */
    private function hasRole(User $user, Bunker $bunker, array $roles): bool
    {
        return BunkerMembership::query()->where(['bunker_id' => $bunker->id, 'user_id' => $user->id, 'status' => 'active'])->whereIn('role', $roles)->whereNotNull('active_key')->exists();
    }
}
