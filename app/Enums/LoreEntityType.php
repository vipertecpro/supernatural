<?php

namespace App\Enums;

enum LoreEntityType: string
{
    case Character = 'character';
    case Performer = 'performer';
    case Creature = 'creature';
    case Species = 'species';
    case Location = 'location';
    case Artifact = 'artifact';
    case Weapon = 'weapon';
    case Spell = 'spell';
    case Ritual = 'ritual';
    case Symbol = 'symbol';
    case Organization = 'organization';
    case Vehicle = 'vehicle';
    case Event = 'event';
    case Concept = 'concept';
}
