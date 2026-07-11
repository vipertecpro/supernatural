<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Enums\PublicationStatus;
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

    public function publish(Model $record, User $actor, bool $isPublic = true): Model
    {
        $this->ensurePublishable($record);

        return DB::transaction(function () use ($record, $actor, $isPublic): Model {
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

            $record->update($attributes);
            $this->auditLogger->record(
                event: 'catalog.'.str($record::class)->classBasename()->snake().'_published',
                auditable: $record,
                metadata: ['status' => PublicationStatus::Published->value],
                actor: $actor,
            );

            return $record->fresh();
        });
    }

    public function archive(Model $record, User $actor): Model
    {
        if ($record->getAttribute('status') === PublicationStatus::Archived) {
            throw new InvalidCatalogOperation('The catalog record is already archived.');
        }

        return DB::transaction(function () use ($record, $actor): Model {
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

            $record->update($attributes);
            $this->auditLogger->record(
                event: 'catalog.'.str($record::class)->classBasename()->snake().'_archived',
                auditable: $record,
                metadata: ['status' => PublicationStatus::Archived->value],
                actor: $actor,
            );

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
}
