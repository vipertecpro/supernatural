<?php

namespace App\Domain\Catalog\Actions;

use App\Enums\PublicationStatus;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkTranslation;

class UpsertWorkTranslation
{
    /** @param array<string, mixed> $attributes */
    public function handle(Work $work, string $locale, array $attributes, User $actor): WorkTranslation
    {
        $normalizedLocale = str($locale)->replace('_', '-')->lower()->toString();
        $translation = $work->translations()->firstOrNew(['locale' => $normalizedLocale]);
        $isNew = ! $translation->exists;

        $translation->fill([
            ...$attributes,
            'locale' => $normalizedLocale,
            'updated_by' => $actor->id,
            ...($isNew ? [
                'status' => PublicationStatus::Draft,
                'published_at' => null,
                'created_by' => $actor->id,
            ] : []),
        ]);
        $translation->save();

        return $translation->fresh();
    }
}
