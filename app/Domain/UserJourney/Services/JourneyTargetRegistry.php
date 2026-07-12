<?php

namespace App\Domain\UserJourney\Services;

use App\Domain\UserJourney\Exceptions\InvalidJourneyOperation;
use App\Enums\PublicationStatus;
use App\Models\Episode;
use App\Models\Franchise;
use App\Models\LoreEntity;
use App\Models\Season;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\Universe;
use App\Models\UserViewingJourney;
use App\Models\Work;
use Illuminate\Database\Eloquent\Model;

class JourneyTargetRegistry
{
    /** @var array<string, class-string<Model>> */
    private const TARGETS = [
        'universe' => Universe::class,
        'franchise' => Franchise::class,
        'work' => Work::class,
        'season' => Season::class,
        'episode' => Episode::class,
        'lore_entity' => LoreEntity::class,
        'timeline' => Timeline::class,
        'timeline_entry' => TimelineEntry::class,
        'user_viewing_journey' => UserViewingJourney::class,
    ];

    /** @return list<string> */
    public function viewingOrderTypes(): array
    {
        return ['work', 'season', 'episode'];
    }

    /** @return list<string> */
    public function watchlistTypes(): array
    {
        return ['work', 'season', 'episode'];
    }

    /** @return list<string> */
    public function favouriteTypes(): array
    {
        return ['universe', 'franchise', 'work', 'season', 'episode', 'lore_entity', 'timeline'];
    }

    /** @return list<string> */
    public function ratingTypes(): array
    {
        return ['work', 'season', 'episode'];
    }

    /** @return list<string> */
    public function noteTypes(): array
    {
        return ['work', 'season', 'episode', 'lore_entity', 'timeline_entry', 'user_viewing_journey'];
    }

    /** @param list<string> $allowed */
    public function resolve(string $type, int $id, array $allowed): Model
    {
        if (! in_array($type, $allowed, true) || ! isset(self::TARGETS[$type])) {
            throw new InvalidJourneyOperation('The selected target type is not supported.', 'invalid_journey_target');
        }

        return self::TARGETS[$type]::query()->findOrFail($id);
    }

    public function universeId(Model $target): int
    {
        return match (true) {
            $target instanceof Universe => $target->id,
            $target instanceof Franchise, $target instanceof Work, $target instanceof LoreEntity, $target instanceof Timeline, $target instanceof UserViewingJourney => (int) $target->getAttribute('universe_id'),
            $target instanceof Season, $target instanceof Episode => (int) $target->work()->value('universe_id'),
            $target instanceof TimelineEntry => (int) $target->timeline()->value('universe_id'),
            default => throw new InvalidJourneyOperation('The target has no supported universe.', 'invalid_journey_target'),
        };
    }

    public function ensurePublic(Model $target): void
    {
        $isPublic = match (true) {
            $target instanceof Universe => Universe::query()->whereKey($target)->where('status', PublicationStatus::Published)->where('is_public', true)->exists(),
            $target instanceof Franchise => Franchise::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof Work => Work::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof Season => Season::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof Episode => Episode::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof LoreEntity => LoreEntity::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof Timeline => Timeline::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof TimelineEntry => TimelineEntry::query()->whereKey($target)->where('status', PublicationStatus::Published)->whereNull('archived_at')->whereHas('timeline', fn ($timeline) => $timeline->where('status', PublicationStatus::Published)->where('visibility', 'public')->whereNull('archived_at'))->exists(),
            $target instanceof UserViewingJourney => true,
            default => false,
        };

        if (! $isPublic) {
            throw new InvalidJourneyOperation('Only published accessible targets may be selected.', 'journey_target_unavailable');
        }
    }

    /** @return array{target: Model, universe_id: int, work_id: int, season_id: int|null, episode_id: int|null, scope_key: string} */
    public function progressPath(string $type, int $id): array
    {
        $target = $this->resolve($type, $id, $this->viewingOrderTypes());
        $this->ensurePublic($target);

        $work = match (true) {
            $target instanceof Work => $target,
            $target instanceof Season, $target instanceof Episode => $target->work,
            default => throw new InvalidJourneyOperation('Progress requires a work, season, or episode.', 'invalid_progress_target'),
        };
        $season = $target instanceof Season ? $target : ($target instanceof Episode ? $target->season : null);
        $episode = $target instanceof Episode ? $target : null;

        if ($target instanceof Episode && ($target->season_id === null || $season === null || $season->work_id !== $work->id)) {
            throw new InvalidJourneyOperation('The episode path is inconsistent.', 'invalid_catalog_boundary');
        }

        return [
            'target' => $target,
            'universe_id' => $work->universe_id,
            'work_id' => $work->id,
            'season_id' => $season?->id,
            'episode_id' => $episode?->id,
            'scope_key' => $type.':'.$id,
        ];
    }
}
