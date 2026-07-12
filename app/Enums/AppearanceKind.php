<?php

namespace App\Enums;

enum AppearanceKind: string
{
    case Appearance = 'appearance';
    case Mention = 'mention';
    case Archive = 'archive';
    case Portrayal = 'portrayal';
}
