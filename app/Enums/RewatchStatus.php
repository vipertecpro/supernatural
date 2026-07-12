<?php

namespace App\Enums;

enum RewatchStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Abandoned = 'abandoned';
}
