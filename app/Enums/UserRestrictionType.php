<?php

namespace App\Enums;

enum UserRestrictionType: string
{
    case Capability = 'capability';
    case PlatformAccess = 'platform_access';
}
