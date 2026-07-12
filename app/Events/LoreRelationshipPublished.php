<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class LoreRelationshipPublished implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly int $loreRelationshipId, public readonly int $actorUserId) {}
}
