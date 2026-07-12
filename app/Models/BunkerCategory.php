<?php

namespace App\Models;

use Database\Factories\BunkerCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BunkerCategory extends Model
{
    /** @use HasFactory<BunkerCategoryFactory> */
    use HasFactory;

    protected $fillable = ['key', 'name', 'description', 'position', 'is_active', 'metadata'];

    /** @return BelongsToMany<Bunker, $this> */
    public function bunkers(): BelongsToMany
    {
        return $this->belongsToMany(Bunker::class, 'bunker_category')->withTimestamps();
    }

    protected function casts(): array
    {
        return ['position' => 'integer', 'is_active' => 'boolean', 'metadata' => 'array'];
    }
}
