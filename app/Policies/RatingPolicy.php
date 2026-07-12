<?php

namespace App\Policies;

use App\Models\Rating;
use App\Models\User;

class RatingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Rating $record): bool
    {
        return $record->user_id === $user->id;
    }

    public function delete(User $user, Rating $record): bool
    {
        return $record->user_id === $user->id;
    }
}
