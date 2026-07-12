<?php

namespace App\Domain\Lore\Services;

use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Enums\LoreEntityType;
use App\Models\EntityAppearance;
use App\Models\Episode;
use App\Models\LoreEntity;
use App\Models\LoreRelationship;
use App\Models\RelationshipType;
use App\Models\Season;
use App\Models\Timeline;
use App\Models\Work;

class LoreIntegrityService
{
    public function normalizeLocale(?string $locale): ?string
    {
        return $locale === null ? null : str($locale)->replace('_', '-')->lower()->toString();
    }

    public function normalizeAlias(string $name): string
    {
        return str($name)->lower()->squish()->toString();
    }

    public function assertAppearance(EntityAppearance $appearance): void
    {
        $entity = $appearance->loreEntity;
        $work = $appearance->work;
        if ($entity->universe_id !== $work->universe_id) {
            throw new InvalidLoreOperation('The appearance entity and work must belong to the same universe.', 'cross_universe_lore_reference');
        }
        if ($appearance->season_id !== null && ! Season::query()->whereKey($appearance->season_id)->where('work_id', $work->id)->exists()) {
            throw new InvalidLoreOperation('The appearance season must belong to the selected work.', 'invalid_catalog_boundary');
        }
        if ($appearance->episode_id !== null) {
            $valid = $appearance->episode()->where('work_id', $work->id)
                ->when($appearance->season_id !== null, fn ($query) => $query->where('season_id', $appearance->season_id))
                ->exists();
            if (! $valid) {
                throw new InvalidLoreOperation('The appearance episode must belong to the selected work and season.', 'invalid_catalog_boundary');
            }
        }
    }

    /** @return array{0: LoreEntity, 1: LoreEntity} */
    public function assertRelationship(LoreEntity $source, LoreEntity $target, RelationshipType $type): array
    {
        if (! $type->is_active) {
            throw new InvalidLoreOperation('The selected relationship type is inactive.', 'invalid_relationship_semantics');
        }
        if ($source->universe_id !== $target->universe_id) {
            throw new InvalidLoreOperation('Relationship endpoints must belong to the same universe.', 'cross_universe_lore_reference');
        }
        if ($source->is($target) && ! $type->allows_self) {
            throw new InvalidLoreOperation('This relationship type does not permit self-relationships.', 'invalid_relationship_semantics');
        }
        $allowed = $type->rules()->where('source_entity_type', $source->type)->where('target_entity_type', $target->type)->exists();
        $reverseAllowed = $type->is_symmetric && $type->rules()->where('source_entity_type', $target->type)->where('target_entity_type', $source->type)->exists();
        if (! $allowed && ! $reverseAllowed) {
            throw new InvalidLoreOperation('The relationship type does not permit these endpoint types.', 'invalid_relationship_semantics');
        }
        if ($type->is_symmetric && $source->id > $target->id) {
            return [$target, $source];
        }

        return [$source, $target];
    }

    /** @param array<string, mixed> $attributes */
    public function assertRelationshipBoundaries(LoreEntity $source, RelationshipType $type, array $attributes): void
    {
        $hasBoundary = collect(['start_work_id', 'start_season_id', 'start_episode_id', 'end_work_id', 'end_season_id', 'end_episode_id'])->contains(fn (string $key): bool => ! empty($attributes[$key]));
        if ($type->requires_catalog_boundary && ! $hasBoundary) {
            throw new InvalidLoreOperation('This relationship type requires a Catalog boundary.', 'invalid_catalog_boundary');
        }
        if (! $type->allows_temporal_bounds && ($hasBoundary || ! empty($attributes['starts_on']) || ! empty($attributes['ends_on']))) {
            throw new InvalidLoreOperation('This relationship type does not permit temporal boundaries.', 'invalid_relationship_semantics');
        }
        foreach (['start', 'end'] as $prefix) {
            $workId = $attributes[$prefix.'_work_id'] ?? null;
            $seasonId = $attributes[$prefix.'_season_id'] ?? null;
            $episodeId = $attributes[$prefix.'_episode_id'] ?? null;
            if ($workId !== null && ! Work::query()->whereKey($workId)->where('universe_id', $source->universe_id)->exists()) {
                throw new InvalidLoreOperation('Relationship Catalog boundaries must belong to the relationship universe.', 'invalid_catalog_boundary');
            }
            if ($seasonId !== null && ! Season::query()->whereKey($seasonId)->where('work_id', $workId)->exists()) {
                throw new InvalidLoreOperation('Relationship seasons must belong to their selected work.', 'invalid_catalog_boundary');
            }
            if ($episodeId !== null && ! Episode::query()->whereKey($episodeId)->where('work_id', $workId)->when($seasonId !== null, fn ($query) => $query->where('season_id', $seasonId))->exists()) {
                throw new InvalidLoreOperation('Relationship episodes must belong to their selected work and season.', 'invalid_catalog_boundary');
            }
        }
        if (! empty($attributes['starts_on']) && ! empty($attributes['ends_on']) && $attributes['starts_on'] > $attributes['ends_on']) {
            throw new InvalidLoreOperation('The relationship start date must not follow its end date.', 'invalid_temporal_boundary');
        }
        $start = $this->catalogPosition($attributes, 'start');
        $end = $this->catalogPosition($attributes, 'end');
        if ($start !== null && $end !== null && $start > $end) {
            throw new InvalidLoreOperation('The relationship start boundary must not follow its end boundary.', 'invalid_temporal_boundary');
        }
    }

    /** @param array<string, mixed> $attributes */
    public function assertTimelineEntry(Timeline $timeline, array $attributes): void
    {
        foreach (['work_id', 'season_id', 'episode_id'] as $key) {
            if (! empty($attributes[$key]) && ! $this->catalogTargetMatchesTimeline($timeline, $key, (int) $attributes[$key], $attributes)) {
                throw new InvalidLoreOperation('Timeline Catalog targets must belong to the timeline universe and parent path.', 'cross_universe_lore_reference');
            }
        }
        if (! empty($attributes['lore_event_entity_id']) && ! LoreEntity::query()->whereKey($attributes['lore_event_entity_id'])->where('universe_id', $timeline->universe_id)->where('type', LoreEntityType::Event)->exists()) {
            throw new InvalidLoreOperation('A timeline lore-event target must be an event in the timeline universe.', 'invalid_timeline_target');
        }
        if (! empty($attributes['lore_relationship_id']) && ! LoreRelationship::query()->whereKey($attributes['lore_relationship_id'])->whereHas('sourceEntity', fn ($query) => $query->where('universe_id', $timeline->universe_id))->exists()) {
            throw new InvalidLoreOperation('A timeline relationship must belong to the timeline universe.', 'invalid_timeline_target');
        }
    }

    /** @param array<string, mixed> $attributes */
    public function assertTimelineOwner(Timeline $timeline, array $attributes): void
    {
        if (! empty($attributes['lore_entity_id']) && ! LoreEntity::query()->whereKey($attributes['lore_entity_id'])->where('universe_id', $timeline->universe_id)->exists()) {
            throw new InvalidLoreOperation('The timeline owner entity must belong to the timeline universe.', 'cross_universe_lore_reference');
        }
        if (! empty($attributes['work_id']) && ! Work::query()->whereKey($attributes['work_id'])->where('universe_id', $timeline->universe_id)->exists()) {
            throw new InvalidLoreOperation('The timeline work must belong to the timeline universe.', 'cross_universe_lore_reference');
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{int, int, int}|null
     */
    private function catalogPosition(array $attributes, string $prefix): ?array
    {
        $workId = $attributes[$prefix.'_work_id'] ?? null;
        if ($workId === null) {
            return null;
        }
        $seasonPosition = isset($attributes[$prefix.'_season_id']) ? (int) Season::query()->whereKey($attributes[$prefix.'_season_id'])->value('position') : -1;
        $episodePosition = isset($attributes[$prefix.'_episode_id']) ? (int) Episode::query()->whereKey($attributes[$prefix.'_episode_id'])->value('position') : -1;

        return [(int) $workId, $seasonPosition, $episodePosition];
    }

    /** @param array<string, mixed> $attributes */
    private function catalogTargetMatchesTimeline(Timeline $timeline, string $key, int $id, array $attributes): bool
    {
        return match ($key) {
            'work_id' => Work::query()->whereKey($id)->where('universe_id', $timeline->universe_id)->exists(),
            'season_id' => Season::query()->whereKey($id)->where('work_id', $attributes['work_id'] ?? 0)->exists(),
            'episode_id' => Episode::query()->whereKey($id)->where('work_id', $attributes['work_id'] ?? 0)->when(! empty($attributes['season_id']), fn ($query) => $query->where('season_id', $attributes['season_id']))->exists(),
            default => false,
        };
    }
}
