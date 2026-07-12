<?php

namespace App\Enums;

enum SpoilerClassificationStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
