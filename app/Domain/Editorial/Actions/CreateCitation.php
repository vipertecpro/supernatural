<?php

namespace App\Domain\Editorial\Actions;

use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Enums\CitationReviewStatus;
use App\Enums\PermissionName;
use App\Models\Citation;
use App\Models\EditorialRevision;
use App\Models\EntityAppearance;
use App\Models\Episode;
use App\Models\Franchise;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Models\LoreEntityTranslation;
use App\Models\LoreRelationship;
use App\Models\RevisionBlock;
use App\Models\RevisionItem;
use App\Models\Season;
use App\Models\Source;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkTranslation;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateCitation
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $sourceIds
     */
    public function handle(Model $target, array $attributes, array $sourceIds, User $actor, ?EditorialRevision $revision = null): Citation
    {
        if (! $this->isSupported($target)) {
            throw new InvalidEditorialOperation('This citation target is not supported.', 'invalid_citation_target');
        }

        if ($revision !== null && ! $this->belongsToRevision($target, $revision)) {
            throw new InvalidEditorialOperation('The citation target does not belong to this revision.', 'cross_revision_citation');
        }

        if (Source::query()->whereKey($sourceIds)->count() !== count(array_unique($sourceIds))) {
            throw new InvalidEditorialOperation('One or more citation sources are invalid.', 'invalid_citation_source');
        }

        return DB::transaction(function () use ($target, $attributes, $sourceIds, $actor): Citation {
            $attributes['review_status'] = $actor->hasPermission(PermissionName::EditorialRevisionsReview)
                ? ($attributes['review_status'] ?? CitationReviewStatus::Pending)
                : CitationReviewStatus::Pending;
            $citation = Citation::query()->create([
                ...$attributes,
                'citable_type' => $target->getMorphClass(),
                'citable_id' => $target->getKey(),
                'added_by_user_id' => $actor->id,
            ]);
            foreach (array_values(array_unique($sourceIds)) as $position => $sourceId) {
                $citation->citationSources()->create(['source_id' => $sourceId, 'relationship' => 'supports', 'position' => $position]);
            }
            $this->auditLogger->record('editorial.citation_created', $citation, [
                'target_type' => $target->getMorphClass(),
                'target_id' => $target->getKey(),
                'source_count' => count($sourceIds),
            ], $actor);

            return $citation->fresh(['citationSources.source']);
        });
    }

    private function belongsToRevision(Model $target, EditorialRevision $revision): bool
    {
        return $target->is($revision)
            || $target instanceof RevisionItem && $target->editorial_revision_id === $revision->id
            || $target instanceof RevisionBlock && $target->editorial_revision_id === $revision->id;
    }

    private function isSupported(Model $target): bool
    {
        return $target instanceof EditorialRevision
            || $target instanceof RevisionItem
            || $target instanceof RevisionBlock
            || $target instanceof Franchise
            || $target instanceof Work
            || $target instanceof WorkTranslation
            || $target instanceof Season
            || $target instanceof Episode
            || $target instanceof LoreEntity
            || $target instanceof LoreEntityTranslation
            || $target instanceof LoreAlias
            || $target instanceof EntityAppearance
            || $target instanceof LoreRelationship
            || $target instanceof Timeline
            || $target instanceof TimelineEntry;
    }
}
