<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use Database\Factories\NotificationDeliveryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $notification_id
 * @property NotificationChannel $channel
 * @property NotificationDeliveryStatus $status
 * @property int $attempt_number
 * @property mixed $retry_at
 * @property UserNotification $notification
 */
class NotificationDelivery extends Model
{
    /** @use HasFactory<NotificationDeliveryFactory> */
    use HasFactory;

    protected $fillable = ['notification_id', 'channel', 'status', 'attempt_number', 'scheduled_at', 'attempted_at', 'delivered_at', 'failed_at', 'suppressed_at', 'provider_response_code', 'failure_code', 'retry_at'];

    /** @return BelongsTo<UserNotification, $this> */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(UserNotification::class, 'notification_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['channel' => NotificationChannel::class, 'status' => NotificationDeliveryStatus::class, 'attempt_number' => 'integer', 'scheduled_at' => 'immutable_datetime', 'attempted_at' => 'immutable_datetime', 'delivered_at' => 'immutable_datetime', 'failed_at' => 'immutable_datetime', 'suppressed_at' => 'immutable_datetime', 'retry_at' => 'immutable_datetime'];
    }
}
