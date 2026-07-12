<?php

namespace App\Enums;

enum BunkerMembershipStatus: string
{
    case Active = 'active';
    case Left = 'left';
    case Removed = 'removed';
    case Banned = 'banned';
}
