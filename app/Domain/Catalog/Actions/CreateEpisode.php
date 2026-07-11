<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Enums\PublicationStatus;
use App\Enums\WorkType;
use App\Models\Episode;
use App\Models\Season;
use App\Models\User;

class CreateEpisode
{
    /** @param array<string, mixed> $attributes */
    public function handle(Season $season, array $attributes, User $actor): Episode
    {
        $season->loadMissing('work');

        if ($season->work->type !== WorkType::Series) {
            throw new InvalidCatalogOperation('Episodes may only belong to series works.');
        }

        return $season->episodes()->create([
            ...$attributes,
            'work_id' => $season->work_id,
            'status' => PublicationStatus::Draft,
            'is_public' => false,
            'published_at' => null,
            'archived_at' => null,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }
}
