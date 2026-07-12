<?php

namespace App\Enums;

enum TimelineEntryType: string
{
    case Entity = 'entity';
    case Work = 'work';
    case Season = 'season';
    case Episode = 'episode';
    case LoreEvent = 'lore_event';
    case Relationship = 'relationship';
    case EditorialMarker = 'editorial_marker';
}
