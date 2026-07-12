<?php

namespace App\Models;

use App\Enums\RightsDecision;
use App\Enums\RightsUseType;
use Carbon\CarbonImmutable;
use Database\Factories\SourceRightsReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $source_id
 * @property RightsUseType $use_type
 * @property RightsDecision $decision
 * @property int|null $supersedes_review_id
 * @property CarbonImmutable $assessed_at
 * @property CarbonImmutable|null $reviewed_at
 * @property CarbonImmutable|null $expires_at
 */
#[Fillable(['source_id', 'use_type', 'decision', 'basis', 'content_license_id', 'rights_holder', 'permission_reference', 'assessed_by_user_id', 'reviewed_by_user_id', 'supersedes_review_id', 'assessed_at', 'reviewed_at', 'expires_at', 'internal_notes'])]
class SourceRightsReview extends Model
{
    /** @use HasFactory<SourceRightsReviewFactory> */
    use HasFactory;

    /** @return BelongsTo<Source, $this> */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /** @return BelongsTo<ContentLicense, $this> */
    public function contentLicense(): BelongsTo
    {
        return $this->belongsTo(ContentLicense::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /** @return BelongsTo<SourceRightsReview, $this> */
    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_review_id');
    }

    public function isEffective(): bool
    {
        return $this->decision->permitsUse() && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'use_type' => RightsUseType::class,
            'decision' => RightsDecision::class,
            'assessed_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
        ];
    }
}
