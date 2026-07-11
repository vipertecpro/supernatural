<?php

namespace App\Domain\Catalog\Actions;

use App\Enums\PublicationStatus;
use App\Models\Franchise;
use App\Models\Universe;
use App\Models\User;

class CreateFranchise
{
    /** @param array<string, mixed> $attributes */
    public function handle(Universe $universe, array $attributes, User $actor): Franchise
    {
        return $universe->franchises()->create([
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
