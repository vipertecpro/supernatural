<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ModerationCaseAssigned implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $moderationCaseId, public readonly int $assignmentId, public readonly int $moderatorUserId) {}
}
