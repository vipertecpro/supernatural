<?php

namespace App\Models;

use App\Enums\ModerationActionType;
use Database\Factories\ModerationActionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

/**
 * @property int $id
 * @property int $moderation_case_id
 * @property int|null $actor_user_id
 * @property ModerationActionType $type
 * @property int|null $target_user_id
 * @property string|null $target_content_type
 * @property int|null $target_content_id
 * @property string $reason_code
 * @property string $user_visible_explanation
 * @property mixed $effective_at
 * @property mixed $expires_at
 * @property ModerationCase $moderationCase
 */
class ModerationAction extends Model
{
    /** @use HasFactory<ModerationActionFactory> */
    use HasFactory;

    protected $fillable = ['moderation_case_id', 'actor_user_id', 'type', 'target_user_id', 'target_content_type', 'target_content_id', 'reason_code', 'user_visible_explanation', 'private_moderator_note', 'effective_at', 'expires_at', 'supersedes_action_id', 'safe_metadata'];

    /** @return BelongsTo<ModerationCase, $this> */
    public function moderationCase(): BelongsTo
    {
        return $this->belongsTo(ModerationCase::class);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** @return MorphTo<Model, $this> */
    public function targetContent(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['type' => ModerationActionType::class, 'effective_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime', 'safe_metadata' => 'array'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Moderation actions are append-only.'));
        static::deleting(fn (): never => throw new LogicException('Moderation actions are append-only.'));
    }

    /** @param Builder<static> $query */
    protected function performUpdate(Builder $query): bool
    {
        throw new LogicException('Moderation actions are append-only.');
    }

    public function delete(): ?bool
    {
        throw new LogicException('Moderation actions are append-only.');
    }
}
