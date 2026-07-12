<?php

namespace App\Models;

use App\Enums\EditorialRevisionStatus;
use Carbon\CarbonImmutable;
use Database\Factories\EditorialRevisionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $revisable_type
 * @property int $revisable_id
 * @property int $author_user_id
 * @property int|null $parent_revision_id
 * @property int $revision_number
 * @property int $base_version
 * @property EditorialRevisionStatus $status
 * @property string $summary
 * @property Model $revisable
 * @property CarbonImmutable|null $submitted_at
 * @property CarbonImmutable|null $review_started_at
 * @property CarbonImmutable|null $decided_at
 * @property CarbonImmutable|null $applied_at
 * @property CarbonImmutable|null $withdrawn_at
 * @property CarbonImmutable|null $superseded_at
 */
#[Fillable(['revisable_type', 'revisable_id', 'author_user_id', 'parent_revision_id', 'revision_number', 'base_version', 'status', 'summary', 'metadata', 'submitted_at', 'review_started_at', 'decided_at', 'applied_at', 'withdrawn_at', 'superseded_at'])]
class EditorialRevision extends Model
{
    /** @use HasFactory<EditorialRevisionFactory> */
    use HasFactory;

    /** @return MorphTo<Model, $this> */
    public function revisable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return BelongsTo<EditorialRevision, $this> */
    public function parentRevision(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_revision_id');
    }

    /** @return HasMany<RevisionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(RevisionItem::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasMany<RevisionBlock, $this> */
    public function blocks(): HasMany
    {
        return $this->hasMany(RevisionBlock::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasMany<ReviewAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    /** @return HasMany<EditorialAction, $this> */
    public function actions(): HasMany
    {
        return $this->hasMany(EditorialAction::class)->orderBy('acted_at')->orderBy('id');
    }

    /** @return MorphMany<Citation, $this> */
    public function citations(): MorphMany
    {
        return $this->morphMany(Citation::class, 'citable');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => EditorialRevisionStatus::class,
            'metadata' => 'array',
            'submitted_at' => 'immutable_datetime',
            'review_started_at' => 'immutable_datetime',
            'decided_at' => 'immutable_datetime',
            'applied_at' => 'immutable_datetime',
            'withdrawn_at' => 'immutable_datetime',
            'superseded_at' => 'immutable_datetime',
        ];
    }
}
