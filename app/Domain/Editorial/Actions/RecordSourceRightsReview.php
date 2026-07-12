<?php

namespace App\Domain\Editorial\Actions;

use App\Enums\PermissionName;
use App\Enums\RightsDecision;
use App\Enums\RightsUseType;
use App\Models\Source;
use App\Models\SourceRightsReview;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class RecordSourceRightsReview
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $attributes */
    public function handle(Source $source, RightsUseType $useType, RightsDecision $decision, array $attributes, User $actor): SourceRightsReview
    {
        return DB::transaction(function () use ($source, $useType, $decision, $attributes, $actor): SourceRightsReview {
            unset($attributes['reviewed_by_user_id'], $attributes['reviewed_at']);
            $previous = $source->rightsReviews()->where('use_type', $useType)->latest('assessed_at')->lockForUpdate()->first();
            $review = $source->rightsReviews()->create([
                ...$attributes,
                'use_type' => $useType,
                'decision' => $decision,
                'assessed_by_user_id' => $actor->id,
                'reviewed_by_user_id' => $actor->hasPermission(PermissionName::EditorialRightsReview) ? $actor->id : null,
                'reviewed_at' => $actor->hasPermission(PermissionName::EditorialRightsReview) ? now() : null,
                'supersedes_review_id' => $previous?->id,
                'assessed_at' => now(),
            ]);
            $this->auditLogger->record($previous === null ? 'editorial.rights_assessment_created' : 'editorial.rights_decision_changed', $review, [
                'source_id' => $source->id,
                'use_type' => $useType->value,
                'decision' => $decision->value,
                'previous_decision' => $previous?->decision->value,
            ], $actor);

            return $review->fresh(['source', 'contentLicense']);
        });
    }
}
