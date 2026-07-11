<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Enums\WorkType;
use App\Models\Franchise;
use App\Models\User;
use App\Models\Work;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateWork
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $attributes */
    public function handle(Work $work, array $attributes, User $actor): Work
    {
        $seriesDetails = $attributes['series_details'] ?? null;
        unset($attributes['series_details']);

        if (array_key_exists('franchise_id', $attributes) && $attributes['franchise_id'] !== null) {
            $matches = Franchise::query()
                ->whereKey($attributes['franchise_id'])
                ->where('universe_id', $work->universe_id)
                ->exists();

            if (! $matches) {
                throw new InvalidCatalogOperation('The franchise must belong to the work universe.');
            }
        }

        $requestedType = isset($attributes['type']) ? WorkType::from($attributes['type']) : $work->type;
        if ($requestedType !== $work->type && ($work->seriesDetail()->exists() || $work->seasons()->exists() || $work->episodes()->exists())) {
            throw new InvalidCatalogOperation('A work type cannot change while type-specific catalog records exist.');
        }

        if (is_array($seriesDetails) && $requestedType !== WorkType::Series) {
            throw new InvalidCatalogOperation('Series details require a series work.');
        }

        return DB::transaction(function () use ($work, $attributes, $seriesDetails, $requestedType, $actor): Work {
            $originalType = $work->type;
            $work->update([
                ...$attributes,
                'updated_by' => $actor->id,
                'lock_version' => $work->lock_version + 1,
            ]);

            if (is_array($seriesDetails)) {
                $work->seriesDetail()->updateOrCreate(['work_id' => $work->id], $seriesDetails);
            }

            if ($requestedType !== $originalType) {
                $this->auditLogger->record('catalog.work_type_changed', $work, [
                    'from' => $originalType->value,
                    'to' => $requestedType->value,
                ], $actor);
            }

            return $work->fresh(['seriesDetail']);
        });
    }
}
