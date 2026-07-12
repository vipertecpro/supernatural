<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Enums\PublicationStatus;
use App\Events\SearchProjectionRemovalRequested;
use App\Events\SearchProjectionRequested;
use App\Models\Episode;
use App\Models\Franchise;
use App\Models\Season;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkTranslation;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TransitionCatalogRecord
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function publish(Model $record, User $actor, bool $isPublic = true, ?int $expectedVersion = null): Model
    {
        return DB::transaction(function () use ($record, $actor, $isPublic, $expectedVersion): Model {
            $record = $this->lockRecord($record);
            $this->ensureVersion($record, $expectedVersion);
            $this->ensurePublishable($record);
            $attributes = [
                'status' => PublicationStatus::Published,
                'published_at' => now(),
            ];
            if ($record->isFillable('is_public')) {
                $attributes['is_public'] = $isPublic;
            }
            if ($record->isFillable('archived_at')) {
                $attributes['archived_at'] = null;
            }
            if ($record->isFillable('updated_by')) {
                $attributes['updated_by'] = $actor->id;
            }
            if ($record->isFillable('lock_version')) {
                $attributes['lock_version'] = (int) $record->getAttribute('lock_version') + 1;
            }

            $record->update($attributes);
            $this->auditLogger->record(
                event: 'catalog.'.str($record::class)->classBasename()->snake().'_published',
                auditable: $record,
                metadata: ['status' => PublicationStatus::Published->value],
                actor: $actor,
            );
            SearchProjectionRequested::dispatch($record->getMorphClass(), (int) $record->getKey());

            return $record->fresh();
        });
    }

    public function archive(Model $record, User $actor, ?int $expectedVersion = null): Model
    {
        return DB::transaction(function () use ($record, $actor, $expectedVersion): Model {
            $record = $this->lockRecord($record);
            $this->ensureVersion($record, $expectedVersion);
            if ($record->getAttribute('status') === PublicationStatus::Archived) {
                throw new InvalidCatalogOperation('The catalog record is already archived.');
            }
            $attributes = ['status' => PublicationStatus::Archived];
            if ($record->isFillable('is_public')) {
                $attributes['is_public'] = false;
            }
            if ($record->isFillable('archived_at')) {
                $attributes['archived_at'] = now();
            }
            if ($record->isFillable('updated_by')) {
                $attributes['updated_by'] = $actor->id;
            }
            if ($record->isFillable('lock_version')) {
                $attributes['lock_version'] = (int) $record->getAttribute('lock_version') + 1;
            }

            $record->update($attributes);
            $this->auditLogger->record(
                event: 'catalog.'.str($record::class)->classBasename()->snake().'_archived',
                auditable: $record,
                metadata: ['status' => PublicationStatus::Archived->value],
                actor: $actor,
            );
            SearchProjectionRemovalRequested::dispatch($record->getMorphClass(), (int) $record->getKey());

            return $record->fresh();
        });
    }

    private function ensurePublishable(Model $record): void
    {
        if ($record->getAttribute('status') === PublicationStatus::Published) {
            throw new InvalidCatalogOperation('The catalog record is already published.');
        }

        $parentIsVisible = match (true) {
            $record instanceof Franchise => $record->universe()->where('status', PublicationStatus::Published)->where('is_public', true)->exists(),
            $record instanceof Work => $record->universe()->where('status', PublicationStatus::Published)->where('is_public', true)->exists()
                && ($record->franchise_id === null || $record->franchise()->visibleToPublic()->exists()),
            $record instanceof Season => $record->work()->visibleToPublic()->exists(),
            $record instanceof Episode => $record->work()->visibleToPublic()->exists()
                && ($record->season_id === null || $record->season()->visibleToPublic()->exists()),
            $record instanceof WorkTranslation => $record->work()->visibleToPublic()->exists(),
            default => false,
        };

        if (! $parentIsVisible) {
            throw new InvalidCatalogOperation('Catalog content cannot be published before its parent is public and published.');
        }
    }

    private function ensureVersion(Model $record, ?int $expectedVersion): void
    {
        if ($record->isFillable('lock_version') && $expectedVersion !== null && (int) $record->getAttribute('lock_version') !== $expectedVersion) {
            throw new OptimisticLockConflict;
        }
    }

    /**
     * @template TModel of Model
     *
     * @param  TModel  $record
     * @return TModel
     */
    private function lockRecord(Model $record): Model
    {
        $record->newQuery()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();

        return $record->refresh();
    }
}
