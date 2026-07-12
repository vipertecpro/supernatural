<?php

namespace App\Enums;

enum ExternalMediaProvider: string
{
    case YouTube = 'youtube';
    case Vimeo = 'vimeo';
    case Spotify = 'spotify';
    case SoundCloud = 'soundcloud';
}
