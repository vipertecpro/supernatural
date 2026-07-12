<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ModerationActionApplied implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $moderationActionId, public readonly int $moderationCaseId, public readonly ?int $targetUserId) {}
}
