<?php

namespace App\Models;

use App\Concerns\HasEditorialRevisions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\CanonClassification;
use App\Enums\DatePrecision;
use App\Enums\LoreRelationshipStatus;
use App\Enums\RelationshipConfidence;
use Carbon\CarbonImmutable;
use Database\Factories\LoreRelationshipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $source_entity_id
 * @property int $target_entity_id
 * @property int $relationship_type_id
 * @property CanonClassification $canon_classification
 * @property RelationshipConfidence $confidence
 * @property LoreRelationshipStatus $status
 * @property int $lock_version
 * @property int|null $start_work_id
 * @property int|null $start_season_id
 * @property int|null $start_episode_id
 * @property int|null $end_work_id
 * @property int|null $end_season_id
 * @property int|null $end_episode_id
 * @property CarbonImmutable|null $starts_on
 * @property CarbonImmutable|null $ends_on
 * @property string|null $qualifier
 * @property LoreEntity $sourceEntity
 * @property LoreEntity $targetEntity
 * @property RelationshipType $relationshipType
 */
class LoreRelationship extends Model
{
    /** @use HasFactory<LoreRelationshipFactory> */
    use HasEditorialRevisions, HasFactory, HasSpoilerConstraints;

    protected $fillable = ['source_entity_id', 'target_entity_id', 'relationship_type_id', 'canon_classification', 'confidence', 'status', 'start_work_id', 'start_season_id', 'start_episode_id', 'end_work_id', 'end_season_id', 'end_episode_id', 'starts_on', 'ends_on', 'date_precision', 'qualifier', 'editorial_note', 'dispute_reason', 'created_by', 'updated_by', 'published_at', 'archived_at', 'lock_version'];

    /** @return BelongsTo<LoreEntity, $this> */
    public function sourceEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class, 'source_entity_id');
    }

    /** @return BelongsTo<LoreEntity, $this> */
    public function targetEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class, 'target_entity_id');
    }

    /** @return BelongsTo<RelationshipType, $this> */
    public function relationshipType(): BelongsTo
    {
        return $this->belongsTo(RelationshipType::class);
    }

    /** @return BelongsTo<Work, $this> */
    public function startWork(): BelongsTo
    {
        return $this->belongsTo(Work::class, 'start_work_id');
    }

    /** @return BelongsTo<Season, $this> */
    public function startSeason(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'start_season_id');
    }

    /** @return BelongsTo<Episode, $this> */
    public function startEpisode(): BelongsTo
    {
        return $this->belongsTo(Episode::class, 'start_episode_id');
    }

    /** @return BelongsTo<Work, $this> */
    public function endWork(): BelongsTo
    {
        return $this->belongsTo(Work::class, 'end_work_id');
    }

    /** @return BelongsTo<Season, $this> */
    public function endSeason(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'end_season_id');
    }

    /** @return BelongsTo<Episode, $this> */
    public function endEpisode(): BelongsTo
    {
        return $this->belongsTo(Episode::class, 'end_episode_id');
    }

    protected function casts(): array
    {
        return ['canon_classification' => CanonClassification::class, 'confidence' => RelationshipConfidence::class, 'status' => LoreRelationshipStatus::class, 'starts_on' => 'immutable_date', 'ends_on' => 'immutable_date', 'date_precision' => DatePrecision::class, 'published_at' => 'immutable_datetime', 'archived_at' => 'immutable_datetime', 'lock_version' => 'integer'];
    }
}
