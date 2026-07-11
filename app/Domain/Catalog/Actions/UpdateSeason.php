<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Season;
use App\Models\User;

class UpdateSeason
{
    /** @param array<string, mixed> $attributes */
    public function handle(Season $season, array $attributes, User $actor): Season
    {
        $season->update([...$attributes, 'updated_by' => $actor->id]);

        return $season->fresh();
    }
}
