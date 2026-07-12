<?php

namespace App\Enums;

enum AppealDecisionType: string
{
    case Upheld = 'upheld';
    case Modified = 'modified';
    case Overturned = 'overturned';
    case Dismissed = 'dismissed';
}
