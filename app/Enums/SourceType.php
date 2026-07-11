<?php

namespace App\Enums;

enum SourceType: string
{
    case Official = 'official';
    case Reference = 'reference';
    case Interview = 'interview';
    case Video = 'video';
    case Social = 'social';
    case Community = 'community';
    case UserContribution = 'user_contribution';
    case Other = 'other';
}
