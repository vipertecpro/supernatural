<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Franchise;
use App\Models\User;

class UpdateFranchise
{
    /** @param array<string, mixed> $attributes */
    public function handle(Franchise $franchise, array $attributes, User $actor): Franchise
    {
        $franchise->update([...$attributes, 'updated_by' => $actor->id]);

        return $franchise->fresh();
    }
}
