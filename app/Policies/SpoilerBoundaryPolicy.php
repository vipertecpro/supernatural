<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\SpoilerBoundary;
use App\Models\User;

class SpoilerBoundaryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::EditorialSpoilersClassify);
    }

    public function view(User $user, SpoilerBoundary $boundary): bool
    {
        return $user->hasPermission(PermissionName::EditorialSpoilersClassify);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::EditorialSpoilersClassify);
    }

    public function update(User $user, SpoilerBoundary $boundary): bool
    {
        return $user->hasPermission(PermissionName::EditorialSpoilersClassify);
    }
}
