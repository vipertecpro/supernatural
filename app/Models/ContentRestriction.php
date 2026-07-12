<?php

namespace App\Models;

use App\Enums\ContentRestrictionType;
use App\Enums\RestrictionStatus;
use Database\Factories\ContentRestrictionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $target_type
 * @property int $target_id
 * @property int $moderation_action_id
 * @property ContentRestrictionType $type
 * @property RestrictionStatus $status
 * @property string $reason_code
 * @property string $public_explanation
 * @property mixed $effective_at
 * @property mixed $expires_at
 * @property mixed $lifted_at
 */
class ContentRestriction extends Model
{
    /** @use HasFactory<ContentRestrictionFactory> */
    use HasFactory;

    protected $fillable = ['target_type', 'target_id', 'moderation_action_id', 'type', 'status', 'effective_at', 'expires_at', 'lifted_at', 'lifted_by_user_id', 'reason_code', 'public_explanation'];

    /** @param Builder<ContentRestriction> $query */
    public function scopeCurrentlyActive(Builder $query): void
    {
        $query->where('status', RestrictionStatus::Active)->where('effective_at', '<=', now())->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /** @return MorphTo<Model, $this> */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<ModerationAction, $this> */
    public function moderationAction(): BelongsTo
    {
        return $this->belongsTo(ModerationAction::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['type' => ContentRestrictionType::class, 'status' => RestrictionStatus::class, 'effective_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime', 'lifted_at' => 'immutable_datetime'];
    }
}
