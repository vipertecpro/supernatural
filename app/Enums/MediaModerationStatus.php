<?php

namespace App\Enums;

enum MediaModerationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Restricted = 'restricted';
    case Rejected = 'rejected';
    case TakedownPending = 'takedown_pending';
    case Removed = 'removed';
}
