<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserFandomPreference;

class UserFandomPreferencePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, UserFandomPreference $record): bool
    {
        return $record->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, UserFandomPreference $record): bool
    {
        return $record->user_id === $user->id;
    }
}
