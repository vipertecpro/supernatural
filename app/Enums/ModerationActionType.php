<?php

namespace App\Enums;

enum ModerationActionType: string
{
    case WarningIssued = 'warning_issued';
    case NoAction = 'no_action';
    case UserRestricted = 'user_restricted';
    case PlatformSuspended = 'platform_suspended';
    case ContentHidden = 'content_hidden';
    case ContentEditingFrozen = 'content_editing_frozen';
    case MediaRestricted = 'media_restricted';
    case TakedownApplied = 'takedown_applied';
    case RestrictionExtended = 'restriction_extended';
    case RestrictionLifted = 'restriction_lifted';
    case CaseDismissed = 'case_dismissed';
    case AdministrativeCorrection = 'administrative_correction';
}
