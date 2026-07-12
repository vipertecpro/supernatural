<?php

namespace App\Enums;

enum EvidenceVisibility: string
{
    case ReporterAndModerators = 'reporter_and_moderators';
    case ModeratorsOnly = 'moderators_only';
}
