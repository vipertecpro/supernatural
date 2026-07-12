<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class UserRestrictionLifted implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $restrictionId, public readonly int $userId, public readonly int $moderationActionId) {}
}
