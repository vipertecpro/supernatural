<?php

namespace App\Enums;

enum ViewingSessionStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Ended = 'ended';
}
