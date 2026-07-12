<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CommunityPostPublished implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $postId, public readonly int $authorUserId) {}
}
