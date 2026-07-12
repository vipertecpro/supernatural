<?php

namespace App\Enums;

enum TimelineType: string
{
    case Universe = 'universe';
    case Work = 'work';
    case Entity = 'entity';
    case Organization = 'organization';
    case Location = 'location';
    case Thematic = 'thematic';
    case Alternate = 'alternate';
    case ReferenceOrder = 'reference_order';
}
