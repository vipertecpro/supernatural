<?php

namespace App\Models;

use App\Enums\TaxonomyScope;
use Database\Factories\EntityTaxonomyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EntityTaxonomy extends Model
{
    /** @use HasFactory<EntityTaxonomyFactory> */
    use HasFactory;

    protected $fillable = ['universe_id', 'scope', 'key', 'name', 'description', 'is_active'];

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return BelongsToMany<LoreEntity, $this> */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(LoreEntity::class, 'entity_taxonomy_items')->withPivot('position')->withTimestamps();
    }

    protected function casts(): array
    {
        return ['scope' => TaxonomyScope::class, 'is_active' => 'boolean'];
    }
}
