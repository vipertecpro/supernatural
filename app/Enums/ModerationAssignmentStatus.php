<?php

namespace App\Enums;

enum ModerationAssignmentStatus: string
{
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
