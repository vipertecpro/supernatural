<?php

namespace App\Enums;

enum BunkerVisibility: string
{
    case Public = 'public';
    case Private = 'private';
    case InviteOnly = 'invite_only';
}
