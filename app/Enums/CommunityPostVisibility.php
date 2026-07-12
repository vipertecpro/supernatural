<?php

namespace App\Enums;

enum CommunityPostVisibility: string
{
    case Public = 'public';
    case Bunker = 'bunker';
    case Members = 'members';
}
