<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class SearchProjectionRemovalRequested implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly string $sourceType, public readonly int $sourceId) {}
}
