<?php

namespace App\Models;

use App\Enums\NotificationLifecycleStatus;
use App\Enums\NotificationPriority;
use Database\Factories\UserNotificationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property int $schema_version
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string $idempotency_key
 * @property NotificationPriority $priority
 * @property NotificationLifecycleStatus $status
 * @property array<string, mixed> $payload
 * @property mixed $read_at
 * @property mixed $archived_at
 * @property mixed $expires_at
 * @property mixed $created_at
 * @property User $user
 * @property Model|null $subject
 */
class UserNotification extends Model
{
    /** @use HasFactory<UserNotificationFactory> */
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = ['user_id', 'type', 'schema_version', 'actor_user_id', 'subject_type', 'subject_id', 'correlation_key', 'idempotency_key', 'priority', 'status', 'payload', 'read_at', 'archived_at', 'expires_at'];

    /** @param Builder<UserNotification> $query */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('archived_at')->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<NotificationDelivery, $this> */
    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class, 'notification_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['schema_version' => 'integer', 'priority' => NotificationPriority::class, 'status' => NotificationLifecycleStatus::class, 'payload' => 'array', 'read_at' => 'immutable_datetime', 'archived_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime'];
    }
}
