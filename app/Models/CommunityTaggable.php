<?php

namespace App\Models;

use Database\Factories\CommunityTaggableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunityTaggable extends Model
{
    /** @use HasFactory<CommunityTaggableFactory> */
    use HasFactory;

    protected $table = 'taggables';

    public $incrementing = false;

    protected $fillable = ['tag_id', 'taggable_type', 'taggable_id'];

    /** @return BelongsTo<CommunityTag, $this> */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(CommunityTag::class);
    }

    /** @return MorphTo<Model, $this> */
    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }
}
