<?php

namespace App\Listeners;

use App\Events\UserBlocked;
use App\Models\CommunityMention;

class DeactivateBlockedCommunityMentions
{
    public function handle(UserBlocked $event): void
    {
        CommunityMention::query()->whereNull('inactive_at')->where(function ($query) use ($event): void {
            $query->where(['mentioning_user_id' => $event->blockerUserId, 'mentioned_user_id' => $event->blockedUserId])
                ->orWhere(['mentioning_user_id' => $event->blockedUserId, 'mentioned_user_id' => $event->blockerUserId]);
        })->update(['inactive_at' => now(), 'notification_key' => null]);
    }
}
