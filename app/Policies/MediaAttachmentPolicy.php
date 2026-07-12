<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\User;

class MediaAttachmentPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::MediaAttach);
    }

    public function update(User $user): bool
    {
        return $user->hasPermission(PermissionName::MediaAttach);
    }

    public function publish(User $user): bool
    {
        return $user->hasPermission(PermissionName::MediaPublish);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission(PermissionName::MediaAttach);
    }
}
