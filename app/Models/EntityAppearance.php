<?php

namespace App\Models;

use App\Concerns\HasEditorialRevisions;
use App\Concerns\HasModerationRestrictions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\AppearanceKind;
use App\Enums\AppearanceSignificance;
use App\Enums\CanonClassification;
use App\Enums\PublicationStatus;
use Database\Factories\EntityAppearanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $lore_entity_id
 * @property int $work_id
 * @property int|null $season_id
 * @property int|null $episode_id
 * @property AppearanceKind $kind
 * @property AppearanceSignificance $significance
 * @property CanonClassification $canon_classification
 * @property PublicationStatus $status
 * @property int $position
 * @property bool|null $is_credited
 * @property string|null $notes
 * @property int $lock_version
 */
class EntityAppearance extends Model
{
    /** @use HasFactory<EntityAppearanceFactory> */
    use HasEditorialRevisions, HasFactory, HasModerationRestrictions, HasSpoilerConstraints;

    protected $fillable = ['lore_entity_id', 'work_id', 'season_id', 'episode_id', 'kind', 'significance', 'is_credited', 'position', 'canon_classification', 'notes', 'status', 'created_by', 'updated_by', 'published_at', 'archived_at', 'lock_version'];

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

    protected function casts(): array
    {
        return ['kind' => AppearanceKind::class, 'significance' => AppearanceSignificance::class, 'canon_classification' => CanonClassification::class, 'status' => PublicationStatus::class, 'is_credited' => 'boolean', 'published_at' => 'immutable_datetime', 'archived_at' => 'immutable_datetime', 'lock_version' => 'integer'];
    }
}
