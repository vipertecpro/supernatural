<?php

namespace App\Enums;

enum EpisodeType: string
{
    case Standard = 'standard';
    case Special = 'special';
    case Pilot = 'pilot';
    case Webisode = 'webisode';
    case Short = 'short';
    case Other = 'other';
}
