<?php

namespace App\Enums;

enum CanonClassification: string
{
    case Official = 'official';
    case Secondary = 'secondary';
    case Community = 'community';
    case Unknown = 'unknown';
}
