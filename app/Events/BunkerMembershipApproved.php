<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class BunkerMembershipApproved implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $joinRequestId, public readonly int $bunkerId, public readonly int $userId) {}
}
