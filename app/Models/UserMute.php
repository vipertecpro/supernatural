<?php

namespace App\Models;

use App\Enums\UserMuteScope;
use Database\Factories\UserMuteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $muting_user_id
 * @property int $muted_user_id
 * @property UserMuteScope $scope
 * @property Carbon|null $expires_at
 * @property User $mutingUser
 * @property User $mutedUser
 */
class UserMute extends Model
{
    /** @use HasFactory<UserMuteFactory> */
    use HasFactory;

    protected $fillable = ['muting_user_id', 'muted_user_id', 'scope', 'expires_at'];

    protected function casts(): array
    {
        return ['scope' => UserMuteScope::class, 'expires_at' => 'immutable_datetime'];
    }

    /** @param Builder<UserMute> $query
     * @return Builder<UserMute>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(fn (Builder $active): Builder => $active->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /** @return BelongsTo<User, $this> */
    public function mutingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muting_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function mutedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muted_user_id');
    }
}
