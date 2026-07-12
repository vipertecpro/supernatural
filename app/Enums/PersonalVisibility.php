<?php

namespace App\Enums;

enum PersonalVisibility: string
{
    case Private = 'private';
    case Followers = 'followers';
    case Public = 'public';
}
