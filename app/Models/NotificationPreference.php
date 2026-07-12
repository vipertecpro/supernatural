<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPreferenceState;
use Database\Factories\NotificationPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property NotificationChannel $channel
 * @property NotificationPreferenceState $state
 * @property int $lock_version
 * @property mixed $updated_at
 */
class NotificationPreference extends Model
{
    /** @use HasFactory<NotificationPreferenceFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'type', 'channel', 'state', 'lock_version'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['channel' => NotificationChannel::class, 'state' => NotificationPreferenceState::class, 'lock_version' => 'integer'];
    }
}
