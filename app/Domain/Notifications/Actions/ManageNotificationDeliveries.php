<?php

namespace App\Domain\Notifications\Actions;

use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationPreferenceState;
use App\Models\NotificationDelivery;
use App\Models\NotificationPreference;
use App\Models\UserNotification;
use App\Notifications\StableNotificationMail;

class ManageNotificationDeliveries
{
    /** @param array{channels:list<NotificationChannel>, mandatory_in_app:bool, mandatory_email:bool, email_disableable:bool, schema_version:int, category:string, priority:mixed, may_expire:bool, spoiler_sensitive:bool, payload_fields:list<string>, active:bool} $definition */
    public function initialize(UserNotification $notification, array $definition): void
    {
        foreach ($definition['channels'] as $channel) {
            if ($channel === NotificationChannel::InApp) {
                NotificationDelivery::query()->create(['notification_id' => $notification->id, 'channel' => $channel, 'status' => NotificationDeliveryStatus::Delivered, 'attempt_number' => 1, 'scheduled_at' => now(), 'attempted_at' => now(), 'delivered_at' => now()]);

                continue;
            }

            $disabled = NotificationPreference::query()->where('user_id', $notification->user_id)->where('type', $notification->type)->where('channel', $channel)->where('state', NotificationPreferenceState::Disabled)->exists();
            if ($disabled && ! $definition['mandatory_email']) {
                NotificationDelivery::query()->create(['notification_id' => $notification->id, 'channel' => $channel, 'status' => NotificationDeliveryStatus::Suppressed, 'attempt_number' => 1, 'scheduled_at' => now(), 'suppressed_at' => now(), 'failure_code' => 'user_preference']);

                continue;
            }

            $delivery = NotificationDelivery::query()->create(['notification_id' => $notification->id, 'channel' => $channel, 'status' => NotificationDeliveryStatus::Queued, 'attempt_number' => 1, 'scheduled_at' => now()]);
            $notification->user->notify((new StableNotificationMail($notification->id, $delivery->id))->afterCommit());
        }
    }

    public function retry(NotificationDelivery $delivery): NotificationDelivery
    {
        if ($delivery->status !== NotificationDeliveryStatus::Failed || $delivery->attempt_number >= (int) config('moderation.notification_delivery_max_attempts', 3)) {
            throw new InvalidModerationOperation('This delivery cannot be retried.', 'notification_retry_unavailable');
        }
        if ($delivery->notification->expires_at?->isPast()) {
            throw new InvalidModerationOperation('Expired notifications cannot be delivered.', 'notification_expired');
        }

        $attempt = NotificationDelivery::query()->create(['notification_id' => $delivery->notification_id, 'channel' => $delivery->channel, 'status' => NotificationDeliveryStatus::Queued, 'attempt_number' => $delivery->attempt_number + 1, 'scheduled_at' => now()]);
        $delivery->notification->user->notify((new StableNotificationMail($delivery->notification_id, $attempt->id))->afterCommit());

        return $attempt;
    }
}
