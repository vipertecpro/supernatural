<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Enums\PublicationStatus;
use App\Enums\WorkType;
use App\Models\Franchise;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use Illuminate\Support\Facades\DB;

class CreateWork
{
    /** @param array<string, mixed> $attributes */
    public function handle(Universe $universe, array $attributes, User $actor): Work
    {
        $seriesDetails = $attributes['series_details'] ?? null;
        unset($attributes['series_details']);

        $this->ensureFranchiseBelongsToUniverse($universe, $attributes['franchise_id'] ?? null);

        return DB::transaction(function () use ($universe, $attributes, $seriesDetails, $actor): Work {
            $work = $universe->works()->create([
                ...$attributes,
                'status' => PublicationStatus::Draft,
                'is_public' => false,
                'published_at' => null,
                'archived_at' => null,
                'lock_version' => 0,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            if (is_array($seriesDetails)) {
                if ($work->type !== WorkType::Series) {
                    throw new InvalidCatalogOperation('Series details require a series work.');
                }

                $work->seriesDetail()->create($seriesDetails);
            }

            return $work->fresh(['seriesDetail']);
        });
    }

    private function ensureFranchiseBelongsToUniverse(Universe $universe, mixed $franchiseId): void
    {
        if ($franchiseId === null) {
            return;
        }

        $matches = Franchise::query()->whereKey($franchiseId)->whereBelongsTo($universe)->exists();

        if (! $matches) {
            throw new InvalidCatalogOperation('The franchise must belong to the selected universe.');
        }
    }
}
