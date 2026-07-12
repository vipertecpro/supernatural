<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Models\Episode;
use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateEpisode
{
    /** @param array<string, mixed> $attributes */
    public function handle(Episode $episode, array $attributes, User $actor): Episode
    {
        $expectedVersion = (int) ($attributes['expected_version'] ?? $episode->lock_version);
        unset($attributes['expected_version']);
        if (isset($attributes['season_id'])) {
            $matches = Season::query()
                ->whereKey($attributes['season_id'])
                ->where('work_id', $episode->work_id)
                ->exists();

            if (! $matches) {
                throw new InvalidCatalogOperation('The episode season must belong to the episode work.');
            }
        }

        return DB::transaction(function () use ($episode, $attributes, $actor, $expectedVersion): Episode {
            $locked = Episode::query()->lockForUpdate()->findOrFail($episode->id);
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            $locked->update([...$attributes, 'updated_by' => $actor->id, 'lock_version' => $expectedVersion + 1]);

            return $locked->fresh();
        });
    }
}
