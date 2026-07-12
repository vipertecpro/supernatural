<?php

namespace App\Enums;

enum MediaOrigin: string
{
    case ProjectOriginal = 'project_original';
    case UserOwned = 'user_owned';
    case Licensed = 'licensed';
}
