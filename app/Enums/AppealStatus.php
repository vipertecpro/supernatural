<?php

namespace App\Enums;

enum AppealStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Decided = 'decided';
    case Withdrawn = 'withdrawn';
}
