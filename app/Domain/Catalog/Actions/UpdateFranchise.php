<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Models\Franchise;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateFranchise
{
    /** @param array<string, mixed> $attributes */
    public function handle(Franchise $franchise, array $attributes, User $actor): Franchise
    {
        $expectedVersion = (int) ($attributes['expected_version'] ?? $franchise->lock_version);
        unset($attributes['expected_version']);

        return DB::transaction(function () use ($franchise, $attributes, $actor, $expectedVersion): Franchise {
            $locked = Franchise::query()->lockForUpdate()->findOrFail($franchise->id);
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            $locked->update([...$attributes, 'updated_by' => $actor->id, 'lock_version' => $expectedVersion + 1]);

            return $locked->fresh();
        });
    }
}
