<?php

namespace App\Enums;

enum ContentRestrictionType: string
{
    case HiddenFromPublic = 'hidden_from_public';
    case HiddenFromSearch = 'hidden_from_search';
    case EditingFrozen = 'editing_frozen';
    case AttachmentsBlocked = 'attachments_blocked';
    case TakedownRestricted = 'takedown_restricted';
    case RightsReviewRequired = 'rights_review_required';
}
