<?php

namespace App\Enums;

enum MediaStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Ready = 'ready';
    case Published = 'published';
    case Archived = 'archived';
    case Restricted = 'restricted';
    case Rejected = 'rejected';
    case Removed = 'removed';
}
