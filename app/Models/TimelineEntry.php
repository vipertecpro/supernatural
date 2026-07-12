<?php

namespace App\Models;

use App\Concerns\HasEditorialRevisions;
use App\Concerns\HasModerationRestrictions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\CanonClassification;
use App\Enums\DatePrecision;
use App\Enums\PublicationStatus;
use App\Enums\RelationshipConfidence;
use App\Enums\TimelineEntryType;
use Carbon\CarbonImmutable;
use Database\Factories\TimelineEntryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $timeline_id
 * @property TimelineEntryType $type
 * @property PublicationStatus $status
 * @property string $sort_key
 * @property int $lock_version
 * @property Timeline $timeline
 * @property Collection<int, LoreEntity> $entities
 * @property string $title
 * @property string|null $summary
 * @property int|null $sequence_number
 * @property CarbonImmutable|null $in_universe_date
 * @property DatePrecision|null $date_precision
 * @property string|null $relative_order
 * @property int|null $work_id
 * @property int|null $season_id
 * @property int|null $episode_id
 * @property int|null $lore_event_entity_id
 * @property int|null $lore_relationship_id
 * @property CanonClassification $canon_classification
 * @property RelationshipConfidence $confidence
 */
class TimelineEntry extends Model
{
    /** @use HasFactory<TimelineEntryFactory> */
    use HasEditorialRevisions, HasFactory, HasModerationRestrictions, HasSpoilerConstraints;

    protected $fillable = ['timeline_id', 'type', 'work_id', 'season_id', 'episode_id', 'lore_event_entity_id', 'lore_relationship_id', 'title', 'summary', 'sort_key', 'sequence_number', 'in_universe_date', 'date_precision', 'relative_order', 'canon_classification', 'confidence', 'status', 'created_by', 'updated_by', 'published_at', 'archived_at', 'lock_version'];

    /** @return BelongsTo<Timeline, $this> */
    public function timeline(): BelongsTo
    {
        return $this->belongsTo(Timeline::class);
    }

    /** @return BelongsTo<Work, $this> */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /** @return BelongsTo<Season, $this> */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /** @return BelongsTo<Episode, $this> */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEventEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class, 'lore_event_entity_id');
    }

    /** @return BelongsTo<LoreRelationship, $this> */
    public function loreRelationship(): BelongsTo
    {
        return $this->belongsTo(LoreRelationship::class);
    }

    /** @return BelongsToMany<LoreEntity, $this> */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(LoreEntity::class, 'timeline_entry_entities')->withPivot(['role', 'position'])->withTimestamps();
    }

    protected function casts(): array
    {
        return ['type' => TimelineEntryType::class, 'in_universe_date' => 'immutable_date', 'date_precision' => DatePrecision::class, 'canon_classification' => CanonClassification::class, 'confidence' => RelationshipConfidence::class, 'status' => PublicationStatus::class, 'sort_key' => 'decimal:6', 'published_at' => 'immutable_datetime', 'archived_at' => 'immutable_datetime', 'lock_version' => 'integer'];
    }
}
