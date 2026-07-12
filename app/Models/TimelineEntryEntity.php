<?php

namespace App\Models;

use Database\Factories\TimelineEntryEntityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineEntryEntity extends Model
{
    /** @use HasFactory<TimelineEntryEntityFactory> */
    use HasFactory;

    protected $fillable = ['timeline_entry_id', 'lore_entity_id', 'role', 'position'];

    /** @return BelongsTo<TimelineEntry, $this> */
    public function timelineEntry(): BelongsTo
    {
        return $this->belongsTo(TimelineEntry::class);
    }

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class);
    }
}
