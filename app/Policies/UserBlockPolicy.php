<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserBlock;

class UserBlockPolicy
{
    public function view(User $user, UserBlock $block): bool
    {
        return $block->blocker_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function delete(User $user, UserBlock $block): bool
    {
        return $this->view($user, $block);
    }
}
