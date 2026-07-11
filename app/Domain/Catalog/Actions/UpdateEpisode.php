<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Models\Episode;
use App\Models\Season;
use App\Models\User;

class UpdateEpisode
{
    /** @param array<string, mixed> $attributes */
    public function handle(Episode $episode, array $attributes, User $actor): Episode
    {
        if (isset($attributes['season_id'])) {
            $matches = Season::query()
                ->whereKey($attributes['season_id'])
                ->where('work_id', $episode->work_id)
                ->exists();

            if (! $matches) {
                throw new InvalidCatalogOperation('The episode season must belong to the episode work.');
            }
        }

        $episode->update([...$attributes, 'updated_by' => $actor->id]);

        return $episode->fresh();
    }
}
