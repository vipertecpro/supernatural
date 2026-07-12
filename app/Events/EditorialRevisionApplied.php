<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class EditorialRevisionApplied implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $revisionId,
        public readonly int $targetId,
        public readonly string $targetType,
        public readonly int $actorUserId,
    ) {}
}
