<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\SourceRightsReview;
use App\Models\User;

class SourceRightsReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::EditorialRightsAssess);
    }

    public function view(User $user, SourceRightsReview $review): bool
    {
        return $user->hasPermission(PermissionName::EditorialRightsAssess);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::EditorialRightsAssess);
    }
}
