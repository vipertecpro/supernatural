<?php

namespace App\Domain\Search\Services;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\SearchProjectionStatus;
use App\Enums\SpoilerVisibility;
use App\Models\SearchDocument;
use App\Models\SearchQuery;
use App\Models\SearchSuggestion;
use App\Models\User;
use App\Models\ViewingProgress;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class SearchQueryService
{
    public function __construct(private readonly SpoilerVisibilityService $spoilers) {}

    /**
     * Execute bounded relational search and spoiler-filter before pagination.
     *
     * @param  array{universe_id?:int,type?:string,locale?:string,canon?:string,page_size?:int,cursor?:string}  $filters
     * @return array{items:list<array<string,mixed>>,next_cursor:?string,has_more:bool}
     */
    public function search(string $query, array $filters, ?User $viewer): array
    {
        $normalized = $this->normalize($query);
        $tokens = array_values(array_filter(explode(' ', $normalized)));
        $universeId = $filters['universe_id'] ?? null;
        $type = $filters['type'] ?? null;
        $locale = $filters['locale'] ?? null;
        $canon = $filters['canon'] ?? null;
        $builder = SearchDocument::query()->where('status', SearchProjectionStatus::Active)->where('visibility', 'public');
        $builder->when($universeId !== null, fn ($items) => $items->where('universe_id', $universeId))
            ->when($type !== null, fn ($items) => $items->where('document_type', $type))
            ->when($locale !== null, fn ($items) => $items->where('locale', $this->normalizeLocale($locale)))
            ->when($canon !== null, fn ($items) => $items->where('canon_classification', $canon));
        foreach ($tokens as $token) {
            $builder->where('normalized_text', 'like', '%'.$token.'%');
        }

        $documents = $builder->limit(250)->get();
        $progressByKey = collect();
        if ($viewer !== null) {
            $scopeKeys = $documents
                ->filter(fn (SearchDocument $document): bool => in_array($document->source_type, ['work', 'season', 'episode'], true))
                ->map(fn (SearchDocument $document): string => $document->source_type.':'.$document->source_id)
                ->values();
            $progressByKey = ViewingProgress::query()->where('user_id', $viewer->id)->where('cycle_key', 0)->whereIn('scope_key', $scopeKeys)->get()->keyBy('scope_key');
        }

        $ranked = $documents->map(function (SearchDocument $document) use ($normalized, $tokens, $viewer, $progressByKey): ?array {
            $source = $this->resolveSource($document);
            if ($source === null) {
                return null;
            }
            $visibility = method_exists($source, 'spoilerConstraints') ? $this->spoilers->decide($source, $viewer) : SpoilerVisibility::Visible;
            if ($visibility === SpoilerVisibility::Hidden) {
                return null;
            }
            $canonical = $this->normalize($document->canonical_title);
            $localized = $this->normalize((string) $document->localized_title);
            $score = match (true) {
                $canonical === $normalized => 1000,
                $localized === $normalized => 950,
                str_starts_with($canonical, $normalized) => 800,
                str_starts_with($localized, $normalized) => 750,
                default => 400 + collect($tokens)->filter(fn (string $token): bool => str_contains($canonical.' '.$localized, $token))->count() * 25,
            } + $document->ranking_weight;
            $progress = $progressByKey->get($document->source_type.':'.$document->source_id);

            $item = [
                '_score' => $score, '_id' => $document->id, 'id' => $document->source_id, 'type' => $document->document_type->value,
                'universe_id' => $document->universe_id, 'title' => $document->localized_title ?? $document->canonical_title, 'canonical_title' => $document->canonical_title,
                'slug' => $document->slug, 'route' => '/api/v1/'.$document->route_key,
                'excerpt' => in_array($visibility, [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true) ? null : $document->searchable_summary,
                'locale' => $document->locale, 'canon_classification' => $document->canon_classification, 'spoiler_visibility' => $visibility->value,
            ];
            if ($viewer !== null) {
                $item['viewing_status'] = $progress?->status->value;
                $item['progress_basis_points'] = $progress?->progress_basis_points;
            }

            return $item;
        })->filter()->sortBy([['_score', 'desc'], ['_id', 'asc']])->values();

        $pageSize = min(max((int) ($filters['page_size'] ?? 20), 1), 50);
        $offset = $this->decodeCursor($filters['cursor'] ?? null);
        $page = $ranked->slice($offset, $pageSize + 1)->values();
        $hasMore = $page->count() > $pageSize;
        $items = array_values($page->take($pageSize)->map(function (array $item): array {
            unset($item['_score'], $item['_id']);

            return $item;
        })->values()->all());

        SearchQuery::query()->create([
            'universe_id' => $universeId, 'query_hash' => hash_hmac('sha256', $normalized, (string) config('app.key')),
            'query_length' => mb_strlen($normalized), 'locale' => $this->normalizeLocale((string) ($filters['locale'] ?? 'en')),
            'document_type' => $type, 'result_count_bucket' => $this->bucket($ranked->count()), 'occurred_at' => now(),
        ]);

        return ['items' => $items, 'next_cursor' => $hasMore ? base64_encode((string) ($offset + $pageSize)) : null, 'has_more' => $hasMore];
    }

    /**
     * @param  array{universe_id?:int,locale?:string,limit?:int}  $filters
     * @return list<array<string,mixed>>
     */
    public function suggestions(string $query, array $filters, ?User $viewer): array
    {
        $normalized = $this->normalize($query);
        $universeId = $filters['universe_id'] ?? null;
        $locale = $filters['locale'] ?? null;

        return array_values(SearchSuggestion::query()->with('searchDocument')->where('normalized_value', 'like', $normalized.'%')
            ->when($universeId !== null, fn ($items) => $items->where('universe_id', $universeId))
            ->when($locale !== null, fn ($items) => $items->where('locale', $this->normalizeLocale($locale)))
            ->orderByDesc('weight')->orderBy('normalized_value')->orderBy('id')->limit(min((int) ($filters['limit'] ?? 10) * 4, 40))->get()
            ->filter(function ($suggestion) use ($viewer): bool {
                $source = $this->resolveSource($suggestion->searchDocument);
                if ($source === null) {
                    return false;
                }

                return ! method_exists($source, 'spoilerConstraints') || $this->spoilers->decide($source, $viewer) !== SpoilerVisibility::Hidden;
            })->take(min((int) ($filters['limit'] ?? 10), 10))->map(fn ($suggestion): array => [
                'value' => $suggestion->value, 'type' => $suggestion->suggestion_type->value,
                'document_type' => $suggestion->searchDocument->document_type->value, 'source_id' => $suggestion->searchDocument->source_id,
            ])->values()->all());
    }

    private function resolveSource(SearchDocument $document): ?Model
    {
        $class = Relation::getMorphedModel($document->source_type);

        return $class === null ? null : $class::query()->find($document->source_id);
    }

    private function normalize(string $value): string
    {
        return str($value)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->limit(200, '')->toString();
    }

    private function normalizeLocale(string $value): string
    {
        return str($value !== '' ? $value : 'en')->replace('_', '-')->lower()->limit(35, '')->toString();
    }

    private function decodeCursor(?string $cursor): int
    {
        if ($cursor === null) {
            return 0;
        }
        $decoded = base64_decode($cursor, true);

        return $decoded !== false && ctype_digit($decoded) ? (int) $decoded : 0;
    }

    private function bucket(int $count): int
    {
        return match (true) {
            $count === 0 => 0, $count <= 5 => 5, $count <= 20 => 20, $count <= 50 => 50, default => 100
        };
    }
}
