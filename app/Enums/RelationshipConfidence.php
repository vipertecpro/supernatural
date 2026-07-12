<?php

namespace App\Enums;

enum RelationshipConfidence: string
{
    case Confirmed = 'confirmed';
    case Probable = 'probable';
    case Possible = 'possible';
    case Disputed = 'disputed';
    case Unknown = 'unknown';
}
