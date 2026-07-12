<?php

namespace App\Enums;

enum LoreRelationshipStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Approved = 'approved';
    case Published = 'published';
    case Disputed = 'disputed';
    case Rejected = 'rejected';
    case Restricted = 'restricted';
    case Archived = 'archived';
}
