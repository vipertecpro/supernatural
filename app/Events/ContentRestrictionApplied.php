<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ContentRestrictionApplied implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $restrictionId, public readonly string $targetType, public readonly int $targetId, public readonly int $moderationActionId) {}
}
