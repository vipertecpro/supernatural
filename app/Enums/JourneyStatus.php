<?php

namespace App\Enums;

enum JourneyStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Abandoned = 'abandoned';
}
