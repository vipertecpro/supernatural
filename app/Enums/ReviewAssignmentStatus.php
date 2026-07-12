<?php

namespace App\Enums;

enum ReviewAssignmentStatus: string
{
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function isActive(): bool
    {
        return in_array($this, [self::Assigned, self::Accepted], true);
    }
}
