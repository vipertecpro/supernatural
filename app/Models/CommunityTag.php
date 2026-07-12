<?php

namespace App\Models;

use Database\Factories\CommunityTagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityTag extends Model
{
    /** @use HasFactory<CommunityTagFactory> */
    use HasFactory;

    protected $table = 'tags';

    protected $fillable = ['universe_id', 'created_by', 'normalized_name', 'display_name', 'slug', 'status'];

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }
}
