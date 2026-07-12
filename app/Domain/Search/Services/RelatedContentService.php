<?php

namespace App\Domain\Search\Services;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\SpoilerVisibility;
use App\Models\EntityTaxonomyItem;
use App\Models\LoreEntity;
use App\Models\SearchDocument;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Relations\Relation;

class RelatedContentService
{
    /** @return list<array<string,mixed>> */
    public function related(string $type, int $id, int $limit, ?User $viewer): array
    {
        $class = Relation::getMorphedModel($type);
        if ($class === null || ! in_array($class, [Work::class, LoreEntity::class], true)) {
            return [];
        }
        $source = $class::query()->findOrFail($id);
        $query = SearchDocument::query()->where('universe_id', $source->universe_id)->whereNot(fn ($items) => $items->where('source_type', $type)->where('source_id', $id));
        if ($source instanceof Work && $source->franchise_id !== null) {
            $query->whereJsonContains('facets->franchise_id', $source->franchise_id);
        } elseif ($source instanceof LoreEntity) {
            $taxonomyIds = $source->taxonomies()->pluck('entity_taxonomies.id');
            $relatedIds = EntityTaxonomyItem::query()->whereIn('entity_taxonomy_id', $taxonomyIds)->where('lore_entity_id', '!=', $source->id)->pluck('lore_entity_id');
            $query->where('source_type', 'lore_entity')->whereIn('source_id', $relatedIds);
        }

        return array_values($query->orderByDesc('ranking_weight')->orderBy('id')->limit(min($limit * 4, 40))->get()->filter(function (SearchDocument $document) use ($viewer): bool {
            $class = Relation::getMorphedModel($document->source_type);
            $model = $class === null ? null : $class::query()->find($document->source_id);

            return $model !== null && (! method_exists($model, 'spoilerConstraints') || app(SpoilerVisibilityService::class)->decide($model, $viewer) !== SpoilerVisibility::Hidden);
        })->take($limit)->map(fn (SearchDocument $document): array => ['id' => $document->source_id, 'type' => $document->document_type->value, 'title' => $document->localized_title ?? $document->canonical_title, 'slug' => $document->slug, 'reason' => $source instanceof Work ? 'same_franchise' : 'shared_taxonomy'])->values()->all());
    }
}
