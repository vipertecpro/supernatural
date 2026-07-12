<?php

namespace App\Enums;

enum ViewingOrderType: string
{
    case Release = 'release';
    case Chronological = 'chronological';
    case Editorial = 'editorial';
    case Franchise = 'franchise';
    case Rewatch = 'rewatch';
}
