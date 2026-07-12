<?php

namespace App\Enums;

enum SpoilerSeverity: string
{
    case None = 'none';
    case Minor = 'minor';
    case Moderate = 'moderate';
    case Major = 'major';
    case Finale = 'finale';

    public function rank(): int
    {
        return match ($this) {
            self::None => 0,
            self::Minor => 1,
            self::Moderate => 2,
            self::Major => 3,
            self::Finale => 4,
        };
    }
}
