<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserMute;

class UserMutePolicy
{
    public function view(User $user, UserMute $mute): bool
    {
        return $mute->muting_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function delete(User $user, UserMute $mute): bool
    {
        return $this->view($user, $mute);
    }
}
