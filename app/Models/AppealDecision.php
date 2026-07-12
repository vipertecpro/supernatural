<?php

namespace App\Models;

use App\Enums\AppealDecisionType;
use Database\Factories\AppealDecisionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property int $id
 * @property int $appeal_id
 * @property int|null $reviewer_user_id
 * @property AppealDecisionType $type
 * @property string $user_visible_explanation
 * @property mixed $decided_at
 */
class AppealDecision extends Model
{
    /** @use HasFactory<AppealDecisionFactory> */
    use HasFactory;

    protected $fillable = ['appeal_id', 'reviewer_user_id', 'type', 'user_visible_explanation', 'private_reviewer_note', 'replacement_action_id', 'decided_at'];

    /** @return BelongsTo<Appeal, $this> */
    public function appeal(): BelongsTo
    {
        return $this->belongsTo(Appeal::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['type' => AppealDecisionType::class, 'decided_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Appeal decisions are immutable.'));
        static::deleting(fn (): never => throw new LogicException('Appeal decisions are immutable.'));
    }

    /** @param Builder<static> $query */
    protected function performUpdate(Builder $query): bool
    {
        throw new LogicException('Appeal decisions are immutable.');
    }

    public function delete(): ?bool
    {
        throw new LogicException('Appeal decisions are immutable.');
    }
}
