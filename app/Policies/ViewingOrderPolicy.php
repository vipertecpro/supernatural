<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\User;
use App\Models\ViewingOrder;

class ViewingOrderPolicy
{
    public function view(?User $user, ViewingOrder $order): bool
    {
        return ViewingOrder::query()->visibleToPublic()->whereKey($order)->exists() || ($user?->hasPermission(PermissionName::JourneyViewingOrdersUpdate) === true);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::JourneyViewingOrdersCreate);
    }

    public function update(User $user): bool
    {
        return $user->hasPermission(PermissionName::JourneyViewingOrdersUpdate);
    }

    public function publish(User $user): bool
    {
        return $user->hasPermission(PermissionName::JourneyViewingOrdersPublish);
    }

    public function archive(User $user): bool
    {
        return $user->hasPermission(PermissionName::JourneyViewingOrdersArchive);
    }
}
