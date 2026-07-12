<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\ModerationCase;
use App\Models\User;

class ModerationCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::ModerationCasesView);
    }

    public function view(User $user, ModerationCase $case): bool
    {
        return $user->hasPermission(PermissionName::ModerationCasesView)
            && ($user->hasPermission(PermissionName::ModerationReportsTriage) || $case->assignments()->where('moderator_user_id', $user->id)->whereNull('cancelled_at')->exists());
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::ModerationCasesCreate);
    }

    public function update(User $user, ModerationCase $case): bool
    {
        return $this->view($user, $case) && $user->hasPermission(PermissionName::ModerationCasesInvestigate);
    }

    public function assign(User $user, ModerationCase $case): bool
    {
        return $user->hasPermission(PermissionName::ModerationCasesAssign) && $this->view($user, $case);
    }

    public function applyAction(User $user, ModerationCase $case): bool
    {
        return $user->hasPermission(PermissionName::ModerationActionsApply) && $this->view($user, $case);
    }
}
