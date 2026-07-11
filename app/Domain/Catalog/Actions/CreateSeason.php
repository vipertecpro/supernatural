<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Enums\PublicationStatus;
use App\Enums\WorkType;
use App\Models\Season;
use App\Models\User;
use App\Models\Work;

class CreateSeason
{
    /** @param array<string, mixed> $attributes */
    public function handle(Work $work, array $attributes, User $actor): Season
    {
        if ($work->type !== WorkType::Series) {
            throw new InvalidCatalogOperation('Seasons may only belong to series works.');
        }

        return $work->seasons()->create([
            ...$attributes,
            'status' => PublicationStatus::Draft,
            'is_public' => false,
            'published_at' => null,
            'archived_at' => null,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }
}
