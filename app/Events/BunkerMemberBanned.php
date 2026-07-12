<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class BunkerMemberBanned implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $banId, public readonly int $bunkerId, public readonly int $userId) {}
}
