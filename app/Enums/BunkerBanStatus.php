<?php

namespace App\Enums;

enum BunkerBanStatus: string
{
    case Active = 'active';
    case Lifted = 'lifted';
    case Expired = 'expired';
}
