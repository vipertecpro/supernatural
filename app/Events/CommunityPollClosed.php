<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CommunityPollClosed implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $pollId, public readonly int $postId, public readonly int $actorUserId) {}
}
