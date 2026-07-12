<?php

namespace App\Enums;

enum CommunityReactionType: string
{
    case Like = 'like';
    case Love = 'love';
    case Insightful = 'insightful';
    case Funny = 'funny';
    case Support = 'support';
}
