<?php

namespace App\Enums;

enum SearchDocumentType: string
{
    case Universe = 'universe';
    case Franchise = 'franchise';
    case Work = 'work';
    case Season = 'season';
    case Episode = 'episode';
    case LoreEntity = 'lore_entity';
    case Timeline = 'timeline';
}
