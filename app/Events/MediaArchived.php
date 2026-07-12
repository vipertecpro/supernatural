<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class MediaArchived implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly string $mediaType, public readonly int $mediaId, public readonly int $actorUserId) {}
}
