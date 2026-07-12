<?php

namespace App\Domain\UserJourney\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\UserJourney\Exceptions\InvalidJourneyOperation;
use App\Domain\UserJourney\Services\JourneyTargetRegistry;
use App\Enums\PersonalVisibility;
use App\Events\WatchlistItemAdded;
use App\Models\Favourite;
use App\Models\PersonalNote;
use App\Models\Rating;
use App\Models\User;
use App\Models\UserFandomPreference;
use App\Models\UserSpoilerPreference;
use App\Models\UserViewingJourney;
use App\Models\ViewingOrder;
use App\Models\Watchlist;
use App\Models\WatchlistItem;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManagePersonalLibrary
{
    public function __construct(
        private readonly JourneyTargetRegistry $targets,
        private readonly AuditLogger $auditLogger,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function createWatchlist(User $user, array $attributes): Watchlist
    {
        return DB::transaction(function () use ($user, $attributes): Watchlist {
            $isDefault = (bool) ($attributes['is_default'] ?? false);
            if ($isDefault) {
                Watchlist::query()->where('user_id', $user->id)->where('is_default', true)->update(['is_default' => false, 'default_key' => null]);
            }

            return Watchlist::query()->create([
                'user_id' => $user->id,
                'universe_id' => $attributes['universe_id'] ?? null,
                'name' => $attributes['name'],
                'slug' => $attributes['slug'] ?? Str::slug((string) $attributes['name']).'-'.Str::lower(Str::random(6)),
                'description' => $attributes['description'] ?? null,
                'visibility' => PersonalVisibility::Private,
                'is_default' => $isDefault,
                'default_key' => $isDefault ? 'default' : null,
                'position' => ((int) Watchlist::query()->where('user_id', $user->id)->max('position')) + 1,
                'lock_version' => 0,
            ]);
        }, attempts: 3);
    }

    /** @param array<string, mixed> $attributes */
    public function updateWatchlist(User $user, Watchlist $watchlist, array $attributes): Watchlist
    {
        return DB::transaction(function () use ($user, $watchlist, $attributes): Watchlist {
            $locked = Watchlist::query()->lockForUpdate()->findOrFail($watchlist->id);
            $this->ensureOwned($user, $locked->user_id);
            $expectedVersion = (int) $attributes['expected_version'];
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            unset($attributes['expected_version']);
            $attributes['visibility'] = PersonalVisibility::Private;
            $attributes['lock_version'] = $expectedVersion + 1;
            $locked->update($attributes);

            return $locked->fresh('items');
        }, attempts: 3);
    }

    /** @param array<string, mixed> $attributes */
    public function addWatchlistItem(User $user, Watchlist $watchlist, array $attributes): WatchlistItem
    {
        $this->ensureOwned($user, $watchlist->user_id);
        $target = $this->targets->resolve((string) $attributes['target_type'], (int) $attributes['target_id'], $this->targets->watchlistTypes());
        $this->targets->ensurePublic($target);
        $universeId = $this->targets->universeId($target);
        if ($watchlist->universe_id !== null && $watchlist->universe_id !== $universeId) {
            throw new InvalidJourneyOperation('The target does not belong to the watchlist universe.', 'cross_universe_watchlist_item');
        }

        if ($watchlist->items()->where('target_type', $attributes['target_type'])->where('target_id', $attributes['target_id'])->exists()) {
            throw new InvalidJourneyOperation('The target is already in this watchlist.', 'duplicate_watchlist_item');
        }

        $item = WatchlistItem::query()->create([
            'watchlist_id' => $watchlist->id,
            'target_type' => $attributes['target_type'],
            'target_id' => $attributes['target_id'],
            'position' => ((int) $watchlist->items()->max('position')) + 1,
            'added_at' => now(),
            'private_note' => $attributes['private_note'] ?? null,
        ]);
        WatchlistItemAdded::dispatch($item->id, $user->id, $item->target_type, $item->target_id);

        return $item;
    }

    public function addFavourite(User $user, string $type, int $id): Favourite
    {
        $target = $this->targets->resolve($type, $id, $this->targets->favouriteTypes());
        $this->targets->ensurePublic($target);

        return Favourite::query()->firstOrCreate(['user_id' => $user->id, 'target_type' => $type, 'target_id' => $id], ['universe_id' => $this->targets->universeId($target)]);
    }

    public function rate(User $user, string $type, int $id, int $value): Rating
    {
        if ($value < 1 || $value > 5) {
            throw new InvalidJourneyOperation('Ratings use the integer scale from 1 to 5.', 'invalid_rating_value');
        }
        $target = $this->targets->resolve($type, $id, $this->targets->ratingTypes());
        $this->targets->ensurePublic($target);

        return Rating::query()->updateOrCreate(['user_id' => $user->id, 'target_type' => $type, 'target_id' => $id], ['universe_id' => $this->targets->universeId($target), 'rating' => $value]);
    }

    /** @param array<string, mixed> $attributes */
    public function createNote(User $user, array $attributes): PersonalNote
    {
        $target = $this->targets->resolve((string) $attributes['target_type'], (int) $attributes['target_id'], $this->targets->noteTypes());
        if ($target instanceof UserViewingJourney && $target->user_id !== $user->id) {
            throw new InvalidJourneyOperation('A journey note may only target an owned journey.', 'note_target_not_owned');
        }
        if (! $target instanceof UserViewingJourney) {
            $this->targets->ensurePublic($target);
        }

        return PersonalNote::query()->create([
            'user_id' => $user->id,
            'universe_id' => $this->targets->universeId($target),
            'target_type' => $attributes['target_type'],
            'target_id' => $attributes['target_id'],
            'title' => isset($attributes['title']) ? strip_tags((string) $attributes['title']) : null,
            'body' => strip_tags((string) $attributes['body']),
            'format' => 'plain_text',
            'visibility' => PersonalVisibility::Private,
            'is_pinned' => (bool) ($attributes['is_pinned'] ?? false),
            'is_spoiler_sensitive' => (bool) ($attributes['is_spoiler_sensitive'] ?? false),
            'lock_version' => 0,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    public function updateNote(User $user, PersonalNote $note, array $attributes): PersonalNote
    {
        return DB::transaction(function () use ($user, $note, $attributes): PersonalNote {
            $locked = PersonalNote::query()->lockForUpdate()->findOrFail($note->id);
            $this->ensureOwned($user, $locked->user_id);
            $expectedVersion = (int) $attributes['expected_version'];
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            unset($attributes['expected_version']);
            foreach (['title', 'body'] as $field) {
                if (isset($attributes[$field])) {
                    $attributes[$field] = strip_tags((string) $attributes[$field]);
                }
            }
            $attributes['visibility'] = PersonalVisibility::Private;
            $attributes['lock_version'] = $expectedVersion + 1;
            $locked->update($attributes);
            $this->auditLogger->record('journey.personal_note_updated', $locked, ['version' => $locked->lock_version], $user);

            return $locked->fresh();
        }, attempts: 3);
    }

    /** @param array<string, mixed> $attributes */
    public function updatePreferences(User $user, int $universeId, array $attributes): UserFandomPreference
    {
        if (isset($attributes['preferred_viewing_order_id'])) {
            $order = ViewingOrder::query()->findOrFail((int) $attributes['preferred_viewing_order_id']);
            if ($order->universe_id !== $universeId) {
                throw new InvalidJourneyOperation('The preferred order must belong to the preference universe.', 'cross_universe_preference');
            }
        }

        return DB::transaction(function () use ($user, $universeId, $attributes): UserFandomPreference {
            $preference = UserFandomPreference::query()->where('user_id', $user->id)->where('universe_id', $universeId)->lockForUpdate()->first();
            $expectedVersion = (int) ($attributes['expected_version'] ?? 0);
            if ($preference !== null && $preference->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            unset($attributes['expected_version']);
            foreach (['continue_watching_visibility', 'rating_visibility', 'favourite_visibility', 'journey_visibility'] as $visibility) {
                $attributes[$visibility] = PersonalVisibility::Private;
            }

            return UserFandomPreference::query()->updateOrCreate(
                ['user_id' => $user->id, 'universe_id' => $universeId],
                [...$attributes, 'lock_version' => $expectedVersion + 1],
            );
        }, attempts: 3);
    }

    /** @param array<string, mixed> $attributes */
    public function updateSpoilerPreferences(User $user, int $universeId, array $attributes): UserSpoilerPreference
    {
        return DB::transaction(function () use ($user, $universeId, $attributes): UserSpoilerPreference {
            $preference = UserSpoilerPreference::query()->where('user_id', $user->id)->where('universe_id', $universeId)->lockForUpdate()->first();
            $expectedVersion = (int) ($attributes['expected_version'] ?? 0);
            if ($preference !== null && $preference->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            unset($attributes['expected_version']);

            $result = UserSpoilerPreference::query()->updateOrCreate(['user_id' => $user->id, 'universe_id' => $universeId], [...$attributes, 'lock_version' => $expectedVersion + 1]);
            $this->auditLogger->record('journey.spoiler_preference_updated', $result, ['version' => $result->lock_version], $user);

            return $result;
        }, attempts: 3);
    }

    private function ensureOwned(User $user, int $ownerId): void
    {
        if ($ownerId !== $user->id) {
            throw new InvalidJourneyOperation('The personal record does not belong to this user.', 'personal_record_not_owned');
        }
    }
}
