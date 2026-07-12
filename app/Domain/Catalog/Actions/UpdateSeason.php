<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateSeason
{
    /** @param array<string, mixed> $attributes */
    public function handle(Season $season, array $attributes, User $actor): Season
    {
        $expectedVersion = (int) ($attributes['expected_version'] ?? $season->lock_version);
        unset($attributes['expected_version']);

        return DB::transaction(function () use ($season, $attributes, $actor, $expectedVersion): Season {
            $locked = Season::query()->lockForUpdate()->findOrFail($season->id);
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            $locked->update([...$attributes, 'updated_by' => $actor->id, 'lock_version' => $expectedVersion + 1]);

            return $locked->fresh();
        });
    }
}
