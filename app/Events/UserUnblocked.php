<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class UserUnblocked implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $blockId, public readonly int $blockerUserId, public readonly int $blockedUserId) {}
}
