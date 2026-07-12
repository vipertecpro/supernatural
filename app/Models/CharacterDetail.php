<?php

namespace App\Models;

use Database\Factories\CharacterDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterDetail extends Model
{
    /** @use HasFactory<CharacterDetailFactory> */
    use HasFactory;

    protected $fillable = ['lore_entity_id', 'category', 'lifecycle_status', 'birth_or_origin', 'pronouns', 'species_entity_id'];

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class);
    }

    /** @return BelongsTo<LoreEntity, $this> */
    public function speciesEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class, 'species_entity_id');
    }
}
