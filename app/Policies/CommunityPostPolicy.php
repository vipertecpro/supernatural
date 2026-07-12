<?php

namespace App\Policies;

use App\Models\CommunityPost;
use App\Models\User;

class CommunityPostPolicy
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
    public function view(User $user, CommunityPost $communityPost): bool
    {
        return $communityPost->bunker === null || $communityPost->bunker->visibility->value === 'public' || $communityPost->bunker->memberships()->where(['user_id' => $user->id, 'status' => 'active'])->whereNotNull('active_key')->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CommunityPost $communityPost): bool
    {
        return $communityPost->author_user_id === $user->id && $communityPost->removed_at === null;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CommunityPost $communityPost): bool
    {
        return $communityPost->author_user_id === $user->id || ($communityPost->bunker !== null && $communityPost->bunker->memberships()->where('user_id', $user->id)->whereIn('role', ['owner', 'administrator', 'moderator'])->where('status', 'active')->exists());
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CommunityPost $communityPost): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CommunityPost $communityPost): bool
    {
        return false;
    }

    public function moderate(User $user, CommunityPost $communityPost): bool
    {
        return $this->delete($user, $communityPost);
    }
}
