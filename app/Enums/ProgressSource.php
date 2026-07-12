<?php

namespace App\Enums;

enum ProgressSource: string
{
    case Manual = 'manual';
    case Playback = 'playback';
    case Session = 'session';
    case Import = 'import';
    case Legacy = 'legacy';
}
