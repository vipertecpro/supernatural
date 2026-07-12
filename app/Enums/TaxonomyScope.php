<?php

namespace App\Enums;

enum TaxonomyScope: string
{
    case Creature = 'creature';
    case Species = 'species';
    case General = 'general';
}
