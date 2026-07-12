<?php

namespace App\Enums;

enum MediaAttachmentRole: string
{
    case Hero = 'hero';
    case Poster = 'poster';
    case Thumbnail = 'thumbnail';
    case Gallery = 'gallery';
    case Avatar = 'avatar';
    case Reference = 'reference';
}
