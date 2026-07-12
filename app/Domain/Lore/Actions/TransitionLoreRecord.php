<?php

namespace App\Domain\Lore\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Enums\CitationReviewStatus;
use App\Enums\LoreRelationshipStatus;
use App\Enums\LoreVisibility;
use App\Enums\PublicationStatus;
use App\Enums\SpoilerClassificationStatus;
use App\Events\LoreEntityPublished;
use App\Events\LoreRelationshipPublished;
use App\Events\SearchProjectionRemovalRequested;
use App\Events\SearchProjectionRequested;
use App\Events\TimelinePublished;
use App\Models\EntityAppearance;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Models\LoreEntityTranslation;
use App\Models\LoreRelationship;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TransitionLoreRecord
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function publish(Model $record, User $actor, int $expectedVersion, bool $isPublic = true): Model
    {
        return DB::transaction(function () use ($record, $actor, $expectedVersion, $isPublic): Model {
            $locked = $record->newQuery()->lockForUpdate()->findOrFail((int) $record->getKey());
            $this->assertVersion($locked, $expectedVersion);
            $this->assertPublishable($locked);
            $status = $locked instanceof LoreRelationship ? LoreRelationshipStatus::Published : PublicationStatus::Published;
            $attributes = ['status' => $status, 'published_at' => now(), 'archived_at' => null, 'updated_by' => $actor->id, 'lock_version' => $expectedVersion + 1];
            if ($locked->isFillable('visibility')) {
                $attributes['visibility'] = $isPublic ? LoreVisibility::Public : LoreVisibility::Restricted;
            }
            $locked->update($attributes);
            $this->auditLogger->record('lore.'.str($locked::class)->classBasename()->snake().'_published', $locked, ['status' => 'published', 'version' => $expectedVersion + 1], $actor);
            match (true) {
                $locked instanceof LoreEntity => LoreEntityPublished::dispatch($locked->id, $actor->id),
                $locked instanceof LoreRelationship => LoreRelationshipPublished::dispatch($locked->id, $actor->id),
                $locked instanceof Timeline => TimelinePublished::dispatch($locked->id, $actor->id),
                default => null,
            };
            SearchProjectionRequested::dispatch($locked->getMorphClass(), (int) $locked->getKey());

            return $locked->fresh();
        });
    }

    public function archive(Model $record, User $actor, int $expectedVersion): Model
    {
        return DB::transaction(function () use ($record, $actor, $expectedVersion): Model {
            $locked = $record->newQuery()->lockForUpdate()->findOrFail((int) $record->getKey());
            $this->assertVersion($locked, $expectedVersion);
            $status = $locked instanceof LoreRelationship ? LoreRelationshipStatus::Archived : PublicationStatus::Archived;
            $locked->update(['status' => $status, 'archived_at' => now(), 'updated_by' => $actor->id, 'lock_version' => $expectedVersion + 1, ...($locked->isFillable('visibility') ? ['visibility' => LoreVisibility::Restricted] : [])]);
            $this->auditLogger->record('lore.'.str($locked::class)->classBasename()->snake().'_archived', $locked, ['status' => 'archived', 'version' => $expectedVersion + 1], $actor);
            SearchProjectionRemovalRequested::dispatch($locked->getMorphClass(), (int) $locked->getKey());

            return $locked->fresh();
        });
    }

    private function assertVersion(Model $record, int $expectedVersion): void
    {
        if ((int) $record->getAttribute('lock_version') !== $expectedVersion) {
            throw new OptimisticLockConflict;
        }
    }

    private function assertPublishable(Model $record): void
    {
        if (in_array($record->getAttribute('status'), [PublicationStatus::Published, LoreRelationshipStatus::Published], true)) {
            throw new InvalidLoreOperation('The Lore record is already published.', 'invalid_lore_transition');
        }
        $parentVisible = match (true) {
            $record instanceof LoreEntity => $record->universe()->where('status', PublicationStatus::Published)->where('is_public', true)->exists(),
            $record instanceof LoreEntityTranslation => $record->loreEntity()->visibleToPublic()->exists(),
            $record instanceof LoreAlias => $record->loreEntity()->visibleToPublic()->exists(),
            $record instanceof EntityAppearance => $record->loreEntity()->visibleToPublic()->exists() && $record->work()->visibleToPublic()->exists(),
            $record instanceof LoreRelationship => $record->sourceEntity()->visibleToPublic()->exists() && $record->targetEntity()->visibleToPublic()->exists(),
            $record instanceof Timeline => $record->universe()->where('status', PublicationStatus::Published)->where('is_public', true)->exists(),
            $record instanceof TimelineEntry => $record->timeline()->visibleToPublic()->exists(),
            default => false,
        };
        if (! $parentVisible) {
            throw new InvalidLoreOperation('Lore content cannot be published before its parents are public and published.', 'invalid_lore_transition');
        }
        if ($record instanceof LoreRelationship) {
            if ($record->relationshipType->requires_citation && ! $record->citations()->where('review_status', CitationReviewStatus::Verified)->exists()) {
                throw new InvalidLoreOperation('This relationship requires a verified citation before publication.', 'lore_evidence_required');
            }
            if ($record->relationshipType->requires_spoiler_classification && ! $record->spoilerConstraints()->where('classification_status', SpoilerClassificationStatus::Approved)->exists()) {
                throw new InvalidLoreOperation('This relationship requires approved spoiler classification before publication.', 'lore_spoiler_classification_required');
            }
        }
    }
}
