<?php

namespace App\Enums;

enum CommunityMentionType: string
{
    case Post = 'post';
    case Comment = 'comment';
}
