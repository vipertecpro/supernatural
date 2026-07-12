<?php

namespace App\Enums;

enum BunkerStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Suspended = 'suspended';
    case Archived = 'archived';
}
