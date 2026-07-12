<?php

namespace App\Models;

use Database\Factories\EntityTaxonomyItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityTaxonomyItem extends Model
{
    /** @use HasFactory<EntityTaxonomyItemFactory> */
    use HasFactory;

    protected $fillable = ['entity_taxonomy_id', 'lore_entity_id', 'position'];

    /** @return BelongsTo<EntityTaxonomy, $this> */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(EntityTaxonomy::class, 'entity_taxonomy_id');
    }

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class);
    }
}
