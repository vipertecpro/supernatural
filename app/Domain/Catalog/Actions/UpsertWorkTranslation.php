<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Enums\PublicationStatus;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkTranslation;
use Illuminate\Support\Facades\DB;

class UpsertWorkTranslation
{
    /** @param array<string, mixed> $attributes */
    public function handle(Work $work, string $locale, array $attributes, User $actor): WorkTranslation
    {
        $expectedVersion = isset($attributes['expected_version']) ? (int) $attributes['expected_version'] : null;
        unset($attributes['expected_version']);
        $normalizedLocale = str($locale)->replace('_', '-')->lower()->toString();

        return DB::transaction(function () use ($work, $attributes, $normalizedLocale, $expectedVersion, $actor): WorkTranslation {
            $translation = $work->translations()->where('locale', $normalizedLocale)->lockForUpdate()->first()
                ?? $work->translations()->make(['locale' => $normalizedLocale]);
            $isNew = ! $translation->exists;
            if (! $isNew && $expectedVersion !== null && $translation->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }

            $translation->fill([
                ...$attributes,
                'locale' => $normalizedLocale,
                'updated_by' => $actor->id,
                'lock_version' => $isNew ? 0 : $translation->lock_version + 1,
                ...($isNew ? [
                    'status' => PublicationStatus::Draft,
                    'published_at' => null,
                    'created_by' => $actor->id,
                ] : []),
            ]);
            $translation->save();

            return $translation->fresh();
        });
    }
}
