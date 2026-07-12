<?php

namespace App\Policies;

use App\Enums\AppealStatus;
use App\Enums\PermissionName;
use App\Models\Appeal;
use App\Models\User;

class AppealPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Appeal $appeal): bool
    {
        return $appeal->appellant_user_id === $user->id || $user->hasPermission(PermissionName::ModerationAppealsReview);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Appeal $appeal): bool
    {
        return $appeal->appellant_user_id === $user->id && $appeal->status === AppealStatus::Submitted;
    }

    public function decide(User $user, Appeal $appeal): bool
    {
        return $user->hasPermission(PermissionName::ModerationAppealsReview) && $appeal->appellant_user_id !== $user->id;
    }
}
