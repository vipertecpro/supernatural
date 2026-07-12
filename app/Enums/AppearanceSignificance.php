<?php

namespace App\Enums;

enum AppearanceSignificance: string
{
    case Primary = 'primary';
    case Supporting = 'supporting';
    case Minor = 'minor';
    case Background = 'background';
    case Unknown = 'unknown';
}
