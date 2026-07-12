<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class AppealDecided implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $appealId, public readonly int $appellantUserId, public readonly int $decisionId, public readonly string $decisionType) {}
}
