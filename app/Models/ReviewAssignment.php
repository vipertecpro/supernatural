<?php

namespace App\Models;

use App\Enums\ReviewAssignmentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ReviewAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $editorial_revision_id
 * @property int $reviewer_user_id
 * @property int $assigned_by_user_id
 * @property ReviewAssignmentStatus $status
 * @property string|null $active_primary_key
 * @property CarbonImmutable $assigned_at
 * @property CarbonImmutable|null $accepted_at
 * @property CarbonImmutable|null $completed_at
 * @property CarbonImmutable|null $cancelled_at
 * @property CarbonImmutable|null $due_at
 */
#[Fillable(['editorial_revision_id', 'reviewer_user_id', 'assigned_by_user_id', 'status', 'is_primary', 'active_primary_key', 'assigned_at', 'accepted_at', 'completed_at', 'cancelled_at', 'due_at', 'internal_note'])]
class ReviewAssignment extends Model
{
    /** @use HasFactory<ReviewAssignmentFactory> */
    use HasFactory;

    /** @return BelongsTo<EditorialRevision, $this> */
    public function revision(): BelongsTo
    {
        return $this->belongsTo(EditorialRevision::class, 'editorial_revision_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => ReviewAssignmentStatus::class,
            'is_primary' => 'boolean',
            'assigned_at' => 'immutable_datetime',
            'accepted_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'due_at' => 'immutable_date',
        ];
    }
}
