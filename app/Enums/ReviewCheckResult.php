<?php

namespace App\Enums;

enum ReviewCheckResult: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case NotRequired = 'not_required';
}
