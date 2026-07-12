<?php

namespace App\Enums;

enum RevisionOperation: string
{
    case Replace = 'replace';
    case Add = 'add';
    case Remove = 'remove';
}
