<?php

namespace App\Enums;

enum WorkReleaseStatus: string
{
    case Announced = 'announced';
    case InProduction = 'in_production';
    case Released = 'released';
    case Cancelled = 'cancelled';
    case Unknown = 'unknown';
}
