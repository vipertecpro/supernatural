<?php

namespace App\Enums;

enum CitationReviewStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
}
