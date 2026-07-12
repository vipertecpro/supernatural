<?php

namespace App\Enums;

enum BunkerRuleCategory: string
{
    case Conduct = 'conduct';
    case Content = 'content';
    case Spoilers = 'spoilers';
    case Safety = 'safety';
    case Other = 'other';
}
