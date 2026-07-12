<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class EditorialRevisionApproved implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $revisionId, public readonly int $actorUserId) {}
}
