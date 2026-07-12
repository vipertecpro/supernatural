<?php

namespace App\Enums;

enum CommunityCommentStatus: string
{
    case Published = 'published';
    case Removed = 'removed';
    case Deleted = 'deleted';
}
