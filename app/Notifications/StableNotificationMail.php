<?php

namespace App\Notifications;

use App\Domain\Notifications\Services\NotificationRenderer;
use App\Enums\NotificationDeliveryStatus;
use App\Models\NotificationDelivery;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StableNotificationMail extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $notificationId, public readonly int $deliveryId) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $notification = UserNotification::query()->findOrFail($this->notificationId);
        $rendered = app(NotificationRenderer::class)->render($notification);
        NotificationDelivery::query()->whereKey($this->deliveryId)->update(['status' => NotificationDeliveryStatus::Sent->value, 'attempted_at' => now()]);

        $mail = (new MailMessage)->subject($rendered['title'])->line($rendered['body']);
        if ($rendered['warning'] !== null) {
            $mail->line($rendered['warning']);
        }

        return $mail;
    }
}
