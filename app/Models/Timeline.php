<?php

namespace App\Models;

use App\Concerns\HasEditorialRevisions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\CanonClassification;
use App\Enums\LoreVisibility;
use App\Enums\PublicationStatus;
use App\Enums\TimelineType;
use Carbon\CarbonImmutable;
use Database\Factories\TimelineFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $universe_id
 * @property int|null $lore_entity_id
 * @property int|null $work_id
 * @property string $name
 * @property TimelineType $type
 * @property CanonClassification $canon_classification
 * @property PublicationStatus $status
 * @property LoreVisibility $visibility
 * @property int|null $created_by
 * @property int $lock_version
 * @property Universe $universe
 * @property string|null $description
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $archived_at
 */
class Timeline extends Model
{
    /** @use HasFactory<TimelineFactory> */
    use HasEditorialRevisions, HasFactory, HasSpoilerConstraints;

    protected $fillable = ['universe_id', 'lore_entity_id', 'work_id', 'name', 'slug', 'type', 'description', 'canon_classification', 'status', 'visibility', 'created_by', 'updated_by', 'published_at', 'archived_at', 'lock_version'];

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class);
    }

    /** @return BelongsTo<Work, $this> */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /** @return HasMany<TimelineEntry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(TimelineEntry::class)->orderBy('sort_key')->orderBy('id');
    }

    /** @param Builder<Timeline> $query
     * @return Builder<Timeline>
     */
    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published)->where('visibility', LoreVisibility::Public)->whereNull('archived_at')->whereHas('universe', fn ($universe) => $universe->where('status', PublicationStatus::Published)->where('is_public', true));
    }

    protected function casts(): array
    {
        return ['type' => TimelineType::class, 'canon_classification' => CanonClassification::class, 'status' => PublicationStatus::class, 'visibility' => LoreVisibility::class, 'published_at' => 'immutable_datetime', 'archived_at' => 'immutable_datetime', 'lock_version' => 'integer'];
    }
}
