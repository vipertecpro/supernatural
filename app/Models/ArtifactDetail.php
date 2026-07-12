<?php

namespace App\Models;

use Database\Factories\ArtifactDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtifactDetail extends Model
{
    /** @use HasFactory<ArtifactDetailFactory> */
    use HasFactory;

    protected $fillable = ['lore_entity_id', 'category', 'function', 'usage_constraints'];

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class);
    }
}
