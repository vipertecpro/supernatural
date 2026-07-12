<?php

namespace App\Enums;

enum BunkerMembershipRole: string
{
    case Owner = 'owner';
    case Administrator = 'administrator';
    case Moderator = 'moderator';
    case Member = 'member';
}
