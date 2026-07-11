<?php

namespace App\Enums;

enum SeriesStatus: string
{
    case Announced = 'announced';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Hiatus = 'hiatus';
    case Unknown = 'unknown';
}
