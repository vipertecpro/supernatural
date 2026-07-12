<?php

namespace App\Enums;

enum SpoilerTolerance: string
{
    case Strict = 'strict';
    case Warn = 'warn';
    case Permissive = 'permissive';
}
