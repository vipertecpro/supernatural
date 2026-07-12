<?php

namespace App\Enums;

enum EditorialActionType: string
{
    case Submitted = 'submitted';
    case Assigned = 'assigned';
    case ReviewStarted = 'review_started';
    case ChangesRequested = 'changes_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Applied = 'applied';
    case Withdrawn = 'withdrawn';
    case Resubmitted = 'resubmitted';
    case Superseded = 'superseded';
    case AssignmentCancelled = 'assignment_cancelled';
}
