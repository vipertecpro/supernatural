<?php

namespace App\Enums;

enum ProgressEventType: string
{
    case Started = 'started';
    case PositionUpdated = 'position_updated';
    case MarkedComplete = 'marked_complete';
    case MarkedIncomplete = 'marked_incomplete';
    case ManuallyCorrected = 'manually_corrected';
    case Reset = 'reset';
    case Imported = 'imported';
    case RewatchStarted = 'rewatch_started';
    case RewatchCompleted = 'rewatch_completed';
}
