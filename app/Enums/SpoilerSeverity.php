<?php

namespace App\Enums;

enum SpoilerSeverity: string
{
    case None = 'none';
    case Mild = 'mild';
    case Major = 'major';
    case Critical = 'critical';
}
