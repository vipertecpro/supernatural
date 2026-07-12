<?php

namespace App\Enums;

enum RightsDecision: string
{
    case Allowed = 'allowed';
    case Prohibited = 'prohibited';
    case Unknown = 'unknown';

    public function permitsUse(): bool
    {
        return $this === self::Allowed;
    }
}
