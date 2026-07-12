<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Report $report): bool
    {
        return $report->reporter_user_id === $user->id || $user->hasPermission(PermissionName::ModerationReportsView);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Report $report): bool
    {
        return $report->reporter_user_id === $user->id && in_array($report->status, [ReportStatus::Submitted, ReportStatus::Linked], true);
    }

    public function delete(User $user, Report $report): bool
    {
        return false;
    }
}
