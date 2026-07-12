<?php

namespace App\Enums;

enum RightsUseType: string
{
    case Linking = 'linking';
    case Embedding = 'embedding';
    case Hosting = 'hosting';
    case Attribution = 'attribution';
    case Commercial = 'commercial';
    case Derivative = 'derivative';
    case Redistribution = 'redistribution';
    case Quotation = 'quotation';
    case Thumbnail = 'thumbnail';
    case MetadataReuse = 'metadata_reuse';
}
