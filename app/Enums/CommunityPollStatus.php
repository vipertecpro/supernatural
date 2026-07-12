<?php

namespace App\Enums;

enum CommunityPollStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
}
