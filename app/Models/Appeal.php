<?php

namespace App\Models;

use App\Enums\AppealStatus;
use Database\Factories\AppealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int|null $appellant_user_id
 * @property int $moderation_case_id
 * @property int $moderation_action_id
 * @property int|null $user_restriction_id
 * @property int|null $content_restriction_id
 * @property AppealStatus $status
 * @property string $explanation
 * @property int $lock_version
 * @property mixed $submitted_at
 * @property mixed $review_started_at
 * @property mixed $decided_at
 * @property mixed $withdrawn_at
 * @property ModerationAction $moderationAction
 * @property AppealDecision|null $decision
 */
class Appeal extends Model
{
    /** @use HasFactory<AppealFactory> */
    use HasFactory;

    protected $fillable = ['appellant_user_id', 'moderation_case_id', 'moderation_action_id', 'user_restriction_id', 'content_restriction_id', 'status', 'active_key', 'explanation', 'submitted_at', 'review_started_at', 'decided_at', 'withdrawn_at', 'lock_version'];

    /** @return BelongsTo<User, $this> */
    public function appellant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appellant_user_id');
    }

    /** @return BelongsTo<ModerationCase, $this> */
    public function moderationCase(): BelongsTo
    {
        return $this->belongsTo(ModerationCase::class);
    }

    /** @return BelongsTo<ModerationAction, $this> */
    public function moderationAction(): BelongsTo
    {
        return $this->belongsTo(ModerationAction::class);
    }

    /** @return HasOne<AppealDecision, $this> */
    public function decision(): HasOne
    {
        return $this->hasOne(AppealDecision::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => AppealStatus::class, 'submitted_at' => 'immutable_datetime', 'review_started_at' => 'immutable_datetime', 'decided_at' => 'immutable_datetime', 'withdrawn_at' => 'immutable_datetime', 'lock_version' => 'integer'];
    }
}
