<?php

namespace App\Models;

use Database\Factories\UserBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $blocker_user_id
 * @property int $blocked_user_id
 * @property string|null $reason_code
 * @property User $blocker
 * @property User $blocked
 */
class UserBlock extends Model
{
    /** @use HasFactory<UserBlockFactory> */
    use HasFactory;

    protected $fillable = ['blocker_user_id', 'blocked_user_id', 'reason_code'];

    /** @return BelongsTo<User, $this> */
    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }
}
