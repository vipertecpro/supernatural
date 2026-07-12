<?php

namespace App\Domain\Search\Services;

use App\Domain\Moderation\Services\RestrictionEvaluator;
use App\Enums\PublicationStatus;
use App\Enums\SearchDocumentType;
use App\Enums\SearchProjectionStatus;
use App\Enums\SearchSuggestionType;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use App\Models\Episode;
use App\Models\Franchise;
use App\Models\LoreEntity;
use App\Models\SearchDocument;
use App\Models\Season;
use App\Models\Timeline;
use App\Models\Universe;
use App\Models\Work;
use Illuminate\Database\Eloquent\Model;

class SearchProjector
{
    public function __construct(private readonly RestrictionEvaluator $restrictions) {}

    /** @var array<class-string<Model>, SearchDocumentType> */
    public const SOURCE_TYPES = [
        Universe::class => SearchDocumentType::Universe,
        Franchise::class => SearchDocumentType::Franchise,
        Work::class => SearchDocumentType::Work,
        Season::class => SearchDocumentType::Season,
        Episode::class => SearchDocumentType::Episode,
        LoreEntity::class => SearchDocumentType::LoreEntity,
        Timeline::class => SearchDocumentType::Timeline,
    ];

    /**
     * Project one authoritative source idempotently.
     *
     * @return array{created:int,updated:int,unchanged:int,skipped:int,removed:int}
     */
    public function project(Model $source, ?string $onlyLocale = null, bool $dryRun = false): array
    {
        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => 0, 'removed' => 0];
        if (! isset(self::SOURCE_TYPES[$source::class]) || ! $this->isPublic($source) || $this->restrictions->isHiddenFromSearch($source)) {
            $counts['removed'] = $this->remove($source, $dryRun);

            return $counts;
        }

        $documents = collect($this->payloads($source))->when($onlyLocale !== null, fn ($items) => $items->where('locale', $this->normalizeLocale($onlyLocale)))->values();
        $keepLocales = $documents->pluck('locale')->all();
        $existingQuery = SearchDocument::query()->where('source_type', $source->getMorphClass())->where('source_id', $source->getKey());
        $obsolete = (clone $existingQuery)->when($keepLocales !== [], fn ($query) => $query->whereNotIn('locale', $keepLocales))->count();
        if (! $dryRun && $obsolete > 0) {
            (clone $existingQuery)->whereNotIn('locale', $keepLocales)->delete();
        }
        $counts['removed'] += $obsolete;

        foreach ($documents as $payload) {
            $document = SearchDocument::query()->where('source_type', $payload['source_type'])->where('source_id', $payload['source_id'])->where('locale', $payload['locale'])->first();
            if ($document === null) {
                $counts['created']++;
            } elseif ($this->isChanged($document, $payload)) {
                $counts['updated']++;
            } else {
                $counts['unchanged']++;
            }
            if (! $dryRun) {
                $document = SearchDocument::query()->updateOrCreate(
                    ['source_type' => $payload['source_type'], 'source_id' => $payload['source_id'], 'locale' => $payload['locale']],
                    $payload,
                );
                $this->syncSuggestions($document, $source);
            }
        }

        return $counts;
    }

    /** Remove derived documents for a source without mutating the source. */
    public function remove(Model $source, bool $dryRun = false): int
    {
        $query = SearchDocument::query()->where('source_type', $source->getMorphClass())->where('source_id', $source->getKey());
        $count = $query->count();
        if (! $dryRun) {
            $query->delete();
        }

        return $count;
    }

    /** @return list<array<string, mixed>> */
    private function payloads(Model $source): array
    {
        $base = $this->baseFields($source);
        $localizations = [['locale' => $base['locale'], 'title' => $base['title'], 'summary' => $base['summary']]];
        if ($source instanceof Work) {
            $source->loadMissing('translations');
            foreach ($source->translations->where('status', PublicationStatus::Published) as $translation) {
                $localizations[] = ['locale' => $translation->locale, 'title' => $translation->title, 'summary' => $translation->summary];
            }
        }
        if ($source instanceof LoreEntity) {
            $source->loadMissing('translations');
            foreach ($source->translations->where('status', PublicationStatus::Published) as $translation) {
                $localizations[] = ['locale' => $translation->locale, 'title' => $translation->name, 'summary' => $translation->summary ?? $translation->short_description];
            }
        }

        $spoiler = $this->spoilerFields($source);

        return array_values(collect($localizations)->unique('locale')->map(function (array $localization) use ($source, $base, $spoiler): array {
            $locale = $this->normalizeLocale((string) $localization['locale']);
            $summary = $spoiler['safe'] ? $this->boundedText($localization['summary']) : null;
            $aliases = $source instanceof LoreEntity ? $source->aliases()->where('status', PublicationStatus::Published)->where('spoiler_sensitive', false)->pluck('name')->all() : [];
            $normalizedText = $this->normalize(implode(' ', array_filter([(string) $base['title'], (string) $localization['title'], $summary, ...$aliases])));

            return [
                'source_type' => $source->getMorphClass(), 'source_id' => (int) $source->getKey(), 'universe_id' => $base['universe_id'], 'locale' => $locale,
                'document_type' => self::SOURCE_TYPES[$source::class], 'canonical_title' => $base['title'], 'localized_title' => $localization['title'],
                'searchable_summary' => $summary, 'normalized_text' => $normalizedText, 'slug' => $base['slug'], 'route_key' => $base['route_key'],
                'status' => SearchProjectionStatus::Active, 'visibility' => 'public', 'canon_classification' => $base['canon'],
                'spoiler_severity' => $spoiler['severity'], 'spoiler_boundary' => $spoiler['boundary'], 'ranking_weight' => $base['weight'],
                'projection_version' => 1, 'source_lock_version' => (int) ($source->getAttribute('lock_version') ?? 0),
                'facets' => $base['facets'], 'safe_metadata' => null, 'freshness_at' => $source->getAttribute('published_at') ?? $source->getAttribute('updated_at'),
                'indexed_at' => now(), 'archived_at' => null,
            ];
        })->values()->all());
    }

    /** @return array{universe_id:int,locale:string,title:string,summary:mixed,slug:string,route_key:string,canon:?string,weight:int,facets:array<string,mixed>} */
    private function baseFields(Model $source): array
    {
        return match (true) {
            $source instanceof Universe => ['universe_id' => $source->id, 'locale' => 'en', 'title' => $source->name, 'summary' => $source->description, 'slug' => $source->slug, 'route_key' => "universes/{$source->id}", 'canon' => null, 'weight' => 100, 'facets' => []],
            $source instanceof Franchise => ['universe_id' => $source->universe_id, 'locale' => 'en', 'title' => $source->name, 'summary' => $source->description, 'slug' => $source->slug, 'route_key' => "franchises/{$source->id}", 'canon' => null, 'weight' => 90, 'facets' => []],
            $source instanceof Work => ['universe_id' => $source->universe_id, 'locale' => $source->original_language, 'title' => $source->original_title, 'summary' => $source->summary, 'slug' => $source->slug, 'route_key' => "works/{$source->id}", 'canon' => $source->canon_status->value, 'weight' => 80, 'facets' => ['work_type' => $source->type->value, 'franchise_id' => $source->franchise_id]],
            $source instanceof Season => ['universe_id' => (int) $source->work()->value('universe_id'), 'locale' => 'en', 'title' => $source->title, 'summary' => $source->summary, 'slug' => $source->slug, 'route_key' => "seasons/{$source->id}", 'canon' => null, 'weight' => 60, 'facets' => ['work_id' => $source->work_id]],
            $source instanceof Episode => ['universe_id' => (int) $source->work()->value('universe_id'), 'locale' => 'en', 'title' => $source->title, 'summary' => $source->summary, 'slug' => $source->slug, 'route_key' => "episodes/{$source->id}", 'canon' => null, 'weight' => 55, 'facets' => ['work_id' => $source->work_id, 'season_id' => $source->season_id]],
            $source instanceof LoreEntity => ['universe_id' => $source->universe_id, 'locale' => $source->original_language, 'title' => $source->canonical_name, 'summary' => $source->short_description ?? $source->summary, 'slug' => $source->slug, 'route_key' => "lore/{$source->id}", 'canon' => $source->canon_classification->value, 'weight' => 75, 'facets' => ['lore_type' => $source->type->value]],
            $source instanceof Timeline => ['universe_id' => $source->universe_id, 'locale' => 'en', 'title' => $source->name, 'summary' => $source->description, 'slug' => $source->slug, 'route_key' => "timelines/{$source->id}", 'canon' => $source->canon_classification->value, 'weight' => 50, 'facets' => ['timeline_type' => $source->type->value]],
            default => throw new \LogicException('Unsupported search projection source.'),
        };
    }

    /** @return array{safe:bool,severity:?string,boundary:list<array{work_id:int,season_id:int|null,episode_id:int|null}>|null} */
    private function spoilerFields(Model $source): array
    {
        if (! method_exists($source, 'spoilerConstraints')) {
            return ['safe' => true, 'severity' => SpoilerSeverity::None->value, 'boundary' => null];
        }
        $source->loadMissing('spoilerConstraints.boundaries');
        $constraints = $source->getRelation('spoilerConstraints')->where('classification_status', SpoilerClassificationStatus::Approved);
        if ($constraints->isEmpty()) {
            return ['safe' => false, 'severity' => SpoilerSeverity::Major->value, 'boundary' => null];
        }
        $constraint = $constraints->sortByDesc(fn ($item): int => $item->severity->rank())->first();
        $boundaries = $constraint->boundaries->map(fn ($boundary): array => ['work_id' => $boundary->work_id, 'season_id' => $boundary->season_id, 'episode_id' => $boundary->episode_id])->values()->all();

        return ['safe' => $constraint->severity === SpoilerSeverity::None, 'severity' => $constraint->severity->value, 'boundary' => $boundaries];
    }

    private function isPublic(Model $source): bool
    {
        if ($source instanceof Universe) {
            return $source->status === PublicationStatus::Published && (bool) $source->is_public;
        }

        return match (true) {
            $source instanceof Franchise => Franchise::query()->visibleToPublic()->whereKey($source)->exists(),
            $source instanceof Work => Work::query()->visibleToPublic()->whereKey($source)->exists(),
            $source instanceof Season => Season::query()->visibleToPublic()->whereKey($source)->exists(),
            $source instanceof Episode => Episode::query()->visibleToPublic()->whereKey($source)->exists(),
            $source instanceof LoreEntity => LoreEntity::query()->visibleToPublic()->whereKey($source)->exists(),
            $source instanceof Timeline => Timeline::query()->visibleToPublic()->whereKey($source)->exists(),
            default => false,
        };
    }

    private function syncSuggestions(SearchDocument $document, Model $source): void
    {
        $document->suggestions()->delete();
        $suggestions = [
            [SearchSuggestionType::CanonicalTitle, $document->canonical_title, 100, false],
            [SearchSuggestionType::Slug, $document->slug, 40, false],
        ];
        if ($document->localized_title !== null && $document->localized_title !== $document->canonical_title) {
            $suggestions[] = [SearchSuggestionType::LocalizedTitle, $document->localized_title, 90, false];
        }
        if ($source instanceof LoreEntity) {
            foreach ($source->aliases()->where('status', PublicationStatus::Published)->where('spoiler_sensitive', false)->get() as $alias) {
                $suggestions[] = [SearchSuggestionType::Alias, $alias->name, 80, false];
            }
        }
        foreach ($suggestions as [$type, $value, $weight, $sensitive]) {
            $document->suggestions()->create(['universe_id' => $document->universe_id, 'locale' => $document->locale, 'suggestion_type' => $type, 'value' => $value, 'normalized_value' => $this->normalize($value), 'weight' => $weight, 'spoiler_sensitive' => $sensitive]);
        }
    }

    /** @param array<string,mixed> $payload */
    private function isChanged(SearchDocument $document, array $payload): bool
    {
        foreach (['canonical_title', 'localized_title', 'searchable_summary', 'normalized_text', 'slug', 'status', 'visibility', 'canon_classification', 'spoiler_severity', 'source_lock_version'] as $field) {
            $value = $payload[$field] instanceof \BackedEnum ? $payload[$field]->value : $payload[$field];
            $current = $document->getRawOriginal($field);
            if ((string) $current !== (string) $value) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return str($value)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->limit(4000, '')->toString();
    }

    private function normalizeLocale(string $locale): string
    {
        return str($locale !== '' ? $locale : 'en')->replace('_', '-')->lower()->limit(35, '')->toString();
    }

    private function boundedText(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? str($value)->stripTags()->squish()->limit(1000, '')->toString() : null;
    }
}
