<?php

namespace App\Enums;

enum CommunityPostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Locked = 'locked';
    case Removed = 'removed';
    case Deleted = 'deleted';
}
