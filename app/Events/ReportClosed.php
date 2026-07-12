<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ReportClosed implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $reportId, public readonly int $reporterUserId, public readonly string $resolutionCode) {}
}
