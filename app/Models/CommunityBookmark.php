<?php

namespace App\Models;

use Database\Factories\CommunityBookmarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $bookmarkable_type
 * @property int $bookmarkable_id
 */
class CommunityBookmark extends Model
{
    /** @use HasFactory<CommunityBookmarkFactory> */
    use HasFactory;

    protected $table = 'bookmarks';

    protected $fillable = ['user_id', 'bookmarkable_type', 'bookmarkable_id'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphTo<Model, $this> */
    public function bookmarkable(): MorphTo
    {
        return $this->morphTo();
    }
}
