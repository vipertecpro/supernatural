<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Watchlist;

class WatchlistPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Watchlist $record): bool
    {
        return $record->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Watchlist $record): bool
    {
        return $record->user_id === $user->id;
    }

    public function delete(User $user, Watchlist $record): bool
    {
        return $record->user_id === $user->id;
    }
}
