<?php

namespace App\Enums;

enum LoreVisibility: string
{
    case Public = 'public';
    case Restricted = 'restricted';
    case Private = 'private';
}
