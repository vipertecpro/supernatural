<?php

namespace App\Policies;

use App\Models\CommunityComment;
use App\Models\User;

class CommunityCommentPolicy
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
    public function view(User $user, CommunityComment $communityComment): bool
    {
        return (new CommunityPostPolicy)->view($user, $communityComment->post);
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
    public function update(User $user, CommunityComment $communityComment): bool
    {
        return $communityComment->author_user_id === $user->id && $communityComment->removed_at === null;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CommunityComment $communityComment): bool
    {
        return $communityComment->author_user_id === $user->id || (new CommunityPostPolicy)->delete($user, $communityComment->post);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CommunityComment $communityComment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CommunityComment $communityComment): bool
    {
        return false;
    }
}
