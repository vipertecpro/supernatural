<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\EditorialRevision;
use App\Models\User;

class EditorialRevisionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::EditorialRevisionsViewOwn)
            || $user->hasPermission(PermissionName::EditorialRevisionsViewAll);
    }

    public function view(User $user, EditorialRevision $revision): bool
    {
        return $user->hasPermission(PermissionName::EditorialRevisionsViewAll)
            || $user->hasPermission(PermissionName::EditorialRevisionsViewOwn) && $revision->author_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::EditorialRevisionsCreate);
    }

    public function update(User $user, EditorialRevision $revision): bool
    {
        return $revision->author_user_id === $user->id
            && $revision->status->isEditable()
            && $user->hasPermission(PermissionName::EditorialRevisionsCreate);
    }

    public function submit(User $user, EditorialRevision $revision): bool
    {
        return $revision->author_user_id === $user->id && $user->hasPermission(PermissionName::EditorialRevisionsCreate);
    }

    public function assign(User $user): bool
    {
        return $user->hasPermission(PermissionName::EditorialRevisionsAssign);
    }

    public function review(User $user, EditorialRevision $revision): bool
    {
        return $revision->author_user_id !== $user->id && $user->hasPermission(PermissionName::EditorialRevisionsReview);
    }

    public function approve(User $user, EditorialRevision $revision): bool
    {
        return $revision->author_user_id !== $user->id && $user->hasPermission(PermissionName::EditorialRevisionsApprove);
    }

    public function apply(User $user): bool
    {
        return $user->hasPermission(PermissionName::EditorialRevisionsApply);
    }

    public function manageCitations(User $user, EditorialRevision $revision): bool
    {
        return $user->hasPermission(PermissionName::EditorialCitationsManage)
            && ($revision->author_user_id === $user->id && $revision->status->isEditable()
                || $user->hasPermission(PermissionName::EditorialRevisionsReview));
    }
}
