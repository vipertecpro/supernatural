<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class UserUnmuted implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $muteId, public readonly int $mutingUserId, public readonly int $mutedUserId, public readonly string $scope) {}
}
