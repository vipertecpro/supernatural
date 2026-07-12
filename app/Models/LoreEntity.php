<?php

namespace App\Models;

use App\Concerns\HasEditorialRevisions;
use App\Concerns\HasModerationRestrictions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\CanonClassification;
use App\Enums\LoreEntityType;
use App\Enums\LoreVisibility;
use App\Enums\PublicationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\LoreEntityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $universe_id
 * @property LoreEntityType $type
 * @property string $slug
 * @property string $canonical_name
 * @property PublicationStatus $status
 * @property CanonClassification $canon_classification
 * @property LoreVisibility $visibility
 * @property int|null $created_by
 * @property int $lock_version
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $archived_at
 */
class LoreEntity extends Model
{
    /** @use HasFactory<LoreEntityFactory> */
    use HasEditorialRevisions, HasFactory, HasModerationRestrictions, HasSpoilerConstraints;

    protected $fillable = ['universe_id', 'type', 'slug', 'canonical_name', 'short_description', 'summary', 'original_language', 'status', 'canon_classification', 'visibility', 'metadata', 'created_by', 'updated_by', 'published_at', 'archived_at', 'lock_version'];

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return HasMany<LoreEntityTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(LoreEntityTranslation::class);
    }

    /** @return HasMany<LoreAlias, $this> */
    public function aliases(): HasMany
    {
        return $this->hasMany(LoreAlias::class);
    }

    /** @return HasMany<EntityAppearance, $this> */
    public function appearances(): HasMany
    {
        return $this->hasMany(EntityAppearance::class);
    }

    /** @return HasMany<LoreRelationship, $this> */
    public function outgoingRelationships(): HasMany
    {
        return $this->hasMany(LoreRelationship::class, 'source_entity_id');
    }

    /** @return HasMany<LoreRelationship, $this> */
    public function incomingRelationships(): HasMany
    {
        return $this->hasMany(LoreRelationship::class, 'target_entity_id');
    }

    /** @return HasMany<Timeline, $this> */
    public function timelines(): HasMany
    {
        return $this->hasMany(Timeline::class);
    }

    /** @return BelongsToMany<TimelineEntry, $this> */
    public function timelineEntries(): BelongsToMany
    {
        return $this->belongsToMany(TimelineEntry::class, 'timeline_entry_entities')->withPivot(['role', 'position'])->withTimestamps();
    }

    /** @return BelongsToMany<EntityTaxonomy, $this> */
    public function taxonomies(): BelongsToMany
    {
        return $this->belongsToMany(EntityTaxonomy::class, 'entity_taxonomy_items')->withPivot('position')->withTimestamps();
    }

    /** @return HasOne<CharacterDetail, $this> */
    public function characterDetail(): HasOne
    {
        return $this->hasOne(CharacterDetail::class);
    }

    /** @return HasOne<PerformerDetail, $this> */
    public function performerDetail(): HasOne
    {
        return $this->hasOne(PerformerDetail::class);
    }

    /** @return HasOne<LocationDetail, $this> */
    public function locationDetail(): HasOne
    {
        return $this->hasOne(LocationDetail::class);
    }

    /** @return HasOne<ArtifactDetail, $this> */
    public function artifactDetail(): HasOne
    {
        return $this->hasOne(ArtifactDetail::class);
    }

    /** @return HasOne<OrganizationDetail, $this> */
    public function organizationDetail(): HasOne
    {
        return $this->hasOne(OrganizationDetail::class);
    }

    /** @return HasOne<LoreEventDetail, $this> */
    public function loreEventDetail(): HasOne
    {
        return $this->hasOne(LoreEventDetail::class);
    }

    /** @return HasOne<ConceptDetail, $this> */
    public function conceptDetail(): HasOne
    {
        return $this->hasOne(ConceptDetail::class);
    }

    /** @param Builder<LoreEntity> $query
     * @return Builder<LoreEntity>
     */
    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published)
            ->where('visibility', LoreVisibility::Public)
            ->whereNull('archived_at')
            ->whereHas('universe', fn ($universe) => $universe->where('status', PublicationStatus::Published)->where('is_public', true))
            ->withoutActivePublicRestriction();
    }

    protected function casts(): array
    {
        return ['type' => LoreEntityType::class, 'status' => PublicationStatus::class, 'canon_classification' => CanonClassification::class, 'visibility' => LoreVisibility::class, 'metadata' => 'array', 'published_at' => 'immutable_datetime', 'archived_at' => 'immutable_datetime', 'lock_version' => 'integer'];
    }
}
