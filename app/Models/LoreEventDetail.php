<?php

namespace App\Models;

use App\Enums\DatePrecision;
use Database\Factories\LoreEventDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoreEventDetail extends Model
{
    /** @use HasFactory<LoreEventDetailFactory> */
    use HasFactory;

    protected $fillable = ['lore_entity_id', 'event_type', 'occurred_on', 'date_precision', 'work_id', 'season_id', 'episode_id'];

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
        return ['occurred_on' => 'immutable_date', 'date_precision' => DatePrecision::class];
    }
}
