<?php

namespace App\Domain\Editorial\Actions;

use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Enums\EditorialRevisionStatus;
use App\Models\EditorialRevision;
use App\Models\EntityAppearance;
use App\Models\Episode;
use App\Models\Franchise;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Models\LoreEntityTranslation;
use App\Models\LoreRelationship;
use App\Models\Season;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkTranslation;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateEditorialRevision
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array{summary: string, parent_revision_id?: int|null, metadata?: array<string, mixed>|null} $attributes */
    public function handle(Model $target, array $attributes, User $author): EditorialRevision
    {
        if (! $this->isSupported($target)) {
            throw new InvalidEditorialOperation('This record does not support editorial revisions.', 'unsupported_revision_target');
        }

        return DB::transaction(function () use ($target, $attributes, $author): EditorialRevision {
            $latestNumber = EditorialRevision::query()
                ->where('revisable_type', $target->getMorphClass())
                ->where('revisable_id', $target->getKey())
                ->lockForUpdate()
                ->max('revision_number');

            $revision = EditorialRevision::query()->create([
                'revisable_type' => $target->getMorphClass(),
                'revisable_id' => $target->getKey(),
                'author_user_id' => $author->id,
                'parent_revision_id' => $attributes['parent_revision_id'] ?? null,
                'revision_number' => ((int) $latestNumber) + 1,
                'base_version' => (int) $target->getAttribute('lock_version'),
                'status' => EditorialRevisionStatus::Draft,
                'summary' => trim($attributes['summary']),
                'metadata' => $attributes['metadata'] ?? null,
            ]);

            $this->auditLogger->record('editorial.revision_created', $revision, [
                'target_type' => $target->getMorphClass(),
                'target_id' => $target->getKey(),
                'revision_number' => $revision->revision_number,
                'base_version' => $revision->base_version,
            ], $author);

            return $revision;
        });
    }

    private function isSupported(Model $target): bool
    {
        return $target instanceof Franchise
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
