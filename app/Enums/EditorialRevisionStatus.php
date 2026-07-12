<?php

namespace App\Enums;

enum EditorialRevisionStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case ChangesRequested = 'changes_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Applied = 'applied';
    case Withdrawn = 'withdrawn';
    case Superseded = 'superseded';

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::ChangesRequested], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Rejected, self::Applied, self::Withdrawn, self::Superseded], true);
    }
}
