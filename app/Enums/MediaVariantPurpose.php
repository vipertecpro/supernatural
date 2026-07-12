<?php

namespace App\Enums;

enum MediaVariantPurpose: string
{
    case Thumbnail = 'thumbnail';
    case Preview = 'preview';
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';
    case Poster = 'poster';
}
