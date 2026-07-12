<?php

namespace App\Models;

use Database\Factories\PerformerDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformerDetail extends Model
{
    /** @use HasFactory<PerformerDetailFactory> */
    use HasFactory;

    protected $fillable = ['lore_entity_id', 'professional_name', 'production_notes'];

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class);
    }
}
