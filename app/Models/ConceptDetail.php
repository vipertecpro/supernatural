<?php

namespace App\Models;

use Database\Factories\ConceptDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConceptDetail extends Model
{
    /** @use HasFactory<ConceptDetailFactory> */
    use HasFactory;

    protected $fillable = ['lore_entity_id', 'category', 'classification'];

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class);
    }
}
