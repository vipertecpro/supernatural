<?php

namespace App\Enums;

enum CanonStatus: string
{
    case Canon = 'canon';
    case NonCanon = 'non_canon';
    case Alternate = 'alternate';
    case Unknown = 'unknown';
}
