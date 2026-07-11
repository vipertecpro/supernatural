<?php

namespace App\Enums;

enum RoleName: string
{
    case Fan = 'fan';
    case Contributor = 'contributor';
    case Moderator = 'moderator';
    case Administrator = 'administrator';

    /**
     * Get the human-readable role label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Fan => 'Fan',
            self::Contributor => 'Contributor',
            self::Moderator => 'Moderator',
            self::Administrator => 'Administrator',
        };
    }
}
