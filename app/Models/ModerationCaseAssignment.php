<?php

namespace App\Models;

use App\Enums\ModerationAssignmentStatus;
use Database\Factories\ModerationCaseAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $moderation_case_id
 * @property int|null $moderator_user_id
 * @property ModerationAssignmentStatus $status
 * @property string $role
 * @property mixed $assigned_at
 * @property mixed $due_at
 */
class ModerationCaseAssignment extends Model
{
    /** @use HasFactory<ModerationCaseAssignmentFactory> */
    use HasFactory;

    protected $fillable = ['moderation_case_id', 'moderator_user_id', 'assigned_by_user_id', 'role', 'status', 'active_primary_key', 'assigned_at', 'accepted_at', 'completed_at', 'cancelled_at', 'due_at', 'private_note'];

    /** @return BelongsTo<ModerationCase, $this> */
    public function moderationCase(): BelongsTo
    {
        return $this->belongsTo(ModerationCase::class);
    }

    /** @return BelongsTo<User, $this> */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_user_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => ModerationAssignmentStatus::class, 'assigned_at' => 'immutable_datetime', 'accepted_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime', 'cancelled_at' => 'immutable_datetime', 'due_at' => 'immutable_datetime'];
    }
}
