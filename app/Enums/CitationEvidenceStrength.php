<?php

namespace App\Enums;

enum CitationEvidenceStrength: string
{
    case Primary = 'primary';
    case Strong = 'strong';
    case Supporting = 'supporting';
    case Weak = 'weak';
}
