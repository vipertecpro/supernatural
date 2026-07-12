<?php

namespace App\Enums;

enum ModerationCaseStatus: string
{
    case Open = 'open';
    case Triaged = 'triaged';
    case Investigating = 'investigating';
    case AwaitingInformation = 'awaiting_information';
    case Actioned = 'actioned';
    case Dismissed = 'dismissed';
    case Closed = 'closed';
}
