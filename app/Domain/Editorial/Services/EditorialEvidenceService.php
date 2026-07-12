<?php

namespace App\Domain\Editorial\Services;

use App\Enums\CitationReviewStatus;
use App\Enums\ReviewCheckResult;
use App\Enums\RightsUseType;
use App\Enums\SpoilerClassificationStatus;
use App\Models\Citation;
use App\Models\EditorialRevision;
use App\Models\RevisionBlock;
use App\Models\RevisionItem;
use App\Models\SourceRightsReview;

class EditorialEvidenceService
{
    public function __construct(private readonly CatalogEditorialFieldRegistry $registry) {}

    public function sourceResult(EditorialRevision $revision): ReviewCheckResult
    {
        $revision->loadMissing(['revisable', 'items.citations.citationSources.source', 'blocks.citations.citationSources.source']);
        $required = $this->requiredEvidence($revision, 'source');

        if ($required === []) {
            return ReviewCheckResult::NotRequired;
        }

        return collect($required)->every(fn (RevisionItem|RevisionBlock $change): bool => $this->hasVerifiedCitation($change, $revision))
            ? ReviewCheckResult::Passed
            : ReviewCheckResult::Failed;
    }

    public function rightsResult(EditorialRevision $revision): ReviewCheckResult
    {
        $revision->loadMissing(['revisable', 'items.citations.citationSources.source.rightsReviews', 'blocks.citations.citationSources.source.rightsReviews']);
        $required = $this->requiredEvidence($revision, 'rights');

        if ($required === []) {
            return ReviewCheckResult::NotRequired;
        }

        return collect($required)->every(function (RevisionItem|RevisionBlock $change): bool {
            $citations = $change->citations->where('review_status', CitationReviewStatus::Verified);

            return $citations->isNotEmpty() && $citations->every(function (Citation $citation): bool {
                return $citation->citationSources->isNotEmpty()
                    && $citation->citationSources->every(function ($link): bool {
                        $review = $link->source->rightsReviews
                            ->where('use_type', RightsUseType::Quotation)
                            ->sortByDesc('assessed_at')
                            ->first();

                        return $review instanceof SourceRightsReview && $review->isEffective();
                    });
            });
        }) ? ReviewCheckResult::Passed : ReviewCheckResult::Failed;
    }

    public function spoilerResult(EditorialRevision $revision): ReviewCheckResult
    {
        $revision->loadMissing(['revisable', 'blocks.spoilerConstraints.boundaries']);
        $required = $this->requiredEvidence($revision, 'spoiler');

        if ($required === []) {
            return ReviewCheckResult::NotRequired;
        }

        return collect($required)->every(function (RevisionItem|RevisionBlock $change): bool {
            if (! $change instanceof RevisionBlock) {
                return false;
            }

            return $change->spoilerConstraints->contains(
                fn ($constraint): bool => $constraint->classification_status === SpoilerClassificationStatus::Approved
                    && $constraint->boundaries->isNotEmpty(),
            );
        }) ? ReviewCheckResult::Passed : ReviewCheckResult::Failed;
    }

    private function hasVerifiedCitation(RevisionItem|RevisionBlock $change, EditorialRevision $revision): bool
    {
        $allowedTypes = $this->registry->definition($revision->revisable, $change->field)['source_types'];

        return $change->citations->contains(
            fn (Citation $citation): bool => $citation->review_status === CitationReviewStatus::Verified
                && $citation->citationSources->contains(
                    fn ($link): bool => in_array($link->source->source_type->value, $allowedTypes, true),
                ),
        );
    }

    /** @return list<RevisionItem|RevisionBlock> */
    private function requiredEvidence(EditorialRevision $revision, string $requirement): array
    {
        $target = $revision->revisable;
        $changes = [...$revision->items->all(), ...$revision->blocks->all()];

        return array_values(array_filter($changes, function (RevisionItem|RevisionBlock $change) use ($target, $requirement): bool {
            return (bool) $this->registry->definition($target, $change->field)[$requirement];
        }));
    }
}
