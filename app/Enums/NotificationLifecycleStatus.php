<?php

namespace App\Enums;

enum NotificationLifecycleStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
    case Expired = 'expired';
}
