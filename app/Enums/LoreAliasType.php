<?php

namespace App\Enums;

enum LoreAliasType: string
{
    case AlternateName = 'alternate_name';
    case Nickname = 'nickname';
    case FormerName = 'former_name';
    case Title = 'title';
    case Codename = 'codename';
    case Translation = 'translation';
    case Misspelling = 'misspelling';
    case ProductionName = 'production_name';
    case Other = 'other';
}
