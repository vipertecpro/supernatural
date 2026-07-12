<?php

namespace App\Models;

use Database\Factories\LocationDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationDetail extends Model
{
    /** @use HasFactory<LocationDetailFactory> */
    use HasFactory;

    protected $fillable = ['lore_entity_id', 'location_type', 'parent_location_entity_id', 'classification'];

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class);
    }

    /** @return BelongsTo<LoreEntity, $this> */
    public function parentLocationEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class, 'parent_location_entity_id');
    }
}
