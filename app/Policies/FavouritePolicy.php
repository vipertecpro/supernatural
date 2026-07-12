<?php

namespace App\Policies;

use App\Models\Favourite;
use App\Models\User;

class FavouritePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Favourite $record): bool
    {
        return $record->user_id === $user->id;
    }
}
