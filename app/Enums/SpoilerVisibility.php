<?php

namespace App\Enums;

enum SpoilerVisibility: string
{
    case Visible = 'visible';
    case Warning = 'visible_with_warning';
    case Redacted = 'redacted';
    case Hidden = 'hidden';
}
