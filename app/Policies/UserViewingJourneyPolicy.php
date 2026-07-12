<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserViewingJourney;

class UserViewingJourneyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, UserViewingJourney $record): bool
    {
        return $record->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, UserViewingJourney $record): bool
    {
        return $record->user_id === $user->id;
    }
}
