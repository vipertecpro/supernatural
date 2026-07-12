<?php

namespace App\Enums;

enum CommunityPollResultsVisibility: string
{
    case Always = 'always';
    case AfterVote = 'after_vote';
    case AfterClose = 'after_close';
}
