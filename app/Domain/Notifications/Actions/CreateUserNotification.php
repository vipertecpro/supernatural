<?php

namespace App\Domain\Notifications\Actions;

use App\Domain\Notifications\Services\NotificationTypeRegistry;
use App\Enums\NotificationLifecycleStatus;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateUserNotification
{
    public function __construct(private readonly NotificationTypeRegistry $types, private readonly ManageNotificationDeliveries $deliveries) {}

    /** @param array<string, mixed> $payload */
    public function execute(User $recipient, string $type, string $idempotencyKey, array $payload, ?Model $subject = null, ?int $actorUserId = null, ?string $correlationKey = null): UserNotification
    {
        $definition = $this->types->definition($type);
        $this->types->validatePayload($type, $payload);

        return DB::transaction(function () use ($recipient, $type, $idempotencyKey, $payload, $subject, $actorUserId, $correlationKey, $definition): UserNotification {
            $notification = UserNotification::query()->firstOrCreate(
                ['user_id' => $recipient->id, 'idempotency_key' => $idempotencyKey],
                ['type' => $type, 'schema_version' => $definition['schema_version'], 'actor_user_id' => $actorUserId, 'subject_type' => $subject?->getMorphClass(), 'subject_id' => $subject?->getKey(), 'correlation_key' => $correlationKey, 'priority' => $definition['priority'], 'status' => NotificationLifecycleStatus::Active, 'payload' => $payload, 'expires_at' => $definition['may_expire'] ? now()->addDays(90) : null],
            );

            if ($notification->wasRecentlyCreated) {
                $this->deliveries->initialize($notification, $definition);
            }

            return $notification->fresh('deliveries');
        });
    }
}
