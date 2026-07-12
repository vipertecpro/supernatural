<?php

namespace App\Models;

use App\Enums\RestrictionStatus;
use App\Enums\UserRestrictionType;
use Database\Factories\UserRestrictionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $moderation_action_id
 * @property UserRestrictionType $type
 * @property RestrictionStatus $status
 * @property string $user_visible_reason
 * @property mixed $effective_at
 * @property mixed $expires_at
 * @property mixed $lifted_at
 */
class UserRestriction extends Model
{
    /** @use HasFactory<UserRestrictionFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'moderation_action_id', 'type', 'status', 'effective_at', 'expires_at', 'lifted_at', 'lifted_by_user_id', 'user_visible_reason'];

    /** @param Builder<UserRestriction> $query */
    public function scopeCurrentlyActive(Builder $query): void
    {
        $query->where('status', RestrictionStatus::Active)->where('effective_at', '<=', now())->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ModerationAction, $this> */
    public function moderationAction(): BelongsTo
    {
        return $this->belongsTo(ModerationAction::class);
    }

    /** @return HasMany<UserRestrictionScope, $this> */
    public function scopes(): HasMany
    {
        return $this->hasMany(UserRestrictionScope::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['type' => UserRestrictionType::class, 'status' => RestrictionStatus::class, 'effective_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime', 'lifted_at' => 'immutable_datetime'];
    }
}
