<?php

namespace App\Models;

use App\Concerns\HasModerationRestrictions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\CommunityCommentStatus;
use Database\Factories\CommunityCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $post_id
 * @property int|null $author_user_id
 * @property int|null $parent_id
 * @property int|null $root_id
 * @property int $depth
 * @property string $body
 * @property CommunityCommentStatus $status
 * @property int $lock_version
 * @property Carbon|null $edited_at
 * @property CommunityPost $post
 */
class CommunityComment extends Model
{
    /** @use HasFactory<CommunityCommentFactory> */
    use HasFactory, HasModerationRestrictions, HasSpoilerConstraints, SoftDeletes;

    protected $table = 'comments';

    protected $fillable = ['post_id', 'author_user_id', 'parent_id', 'root_id', 'depth', 'body', 'body_checksum', 'status', 'lock_version', 'edited_at', 'removed_at'];

    /** @return BelongsTo<CommunityPost, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return BelongsTo<CommunityComment, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<CommunityComment, $this> */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    protected function casts(): array
    {
        return ['status' => CommunityCommentStatus::class, 'depth' => 'integer', 'lock_version' => 'integer', 'edited_at' => 'immutable_datetime', 'removed_at' => 'immutable_datetime'];
    }
}
