<?php

namespace App\Enums;

enum RestrictionStatus: string
{
    case Active = 'active';
    case Lifted = 'lifted';
    case Expired = 'expired';
}
