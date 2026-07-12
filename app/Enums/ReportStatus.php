<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Submitted = 'submitted';
    case Triaged = 'triaged';
    case Linked = 'linked';
    case Withdrawn = 'withdrawn';
    case Closed = 'closed';
}
