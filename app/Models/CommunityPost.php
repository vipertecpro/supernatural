<?php

namespace App\Models;

use App\Concerns\HasModerationRestrictions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\CommunityPostStatus;
use App\Enums\CommunityPostVisibility;
use Database\Factories\CommunityPostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $author_user_id
 * @property int|null $bunker_id
 * @property int $universe_id
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string|null $title
 * @property string $body
 * @property CommunityPostStatus $status
 * @property CommunityPostVisibility $visibility
 * @property bool $comments_enabled
 * @property int $lock_version
 * @property Carbon|null $locked_at
 * @property Carbon|null $edited_at
 * @property Carbon|null $published_at
 * @property Bunker|null $bunker
 * @property User|null $author
 */
class CommunityPost extends Model
{
    /** @use HasFactory<CommunityPostFactory> */
    use HasFactory, HasModerationRestrictions, HasSpoilerConstraints, SoftDeletes;

    protected $table = 'posts';

    protected $fillable = ['author_user_id', 'bunker_id', 'universe_id', 'reference_type', 'reference_id', 'title', 'body', 'body_checksum', 'status', 'visibility', 'comments_enabled', 'lock_version', 'locked_at', 'edited_at', 'published_at', 'removed_at'];

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return BelongsTo<Bunker, $this> */
    public function bunker(): BelongsTo
    {
        return $this->belongsTo(Bunker::class);
    }

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return MorphTo<Model, $this> */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<CommunityComment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(CommunityComment::class, 'post_id');
    }

    /** @return HasMany<CommunityPoll, $this> */
    public function polls(): HasMany
    {
        return $this->hasMany(CommunityPoll::class, 'post_id');
    }

    protected function casts(): array
    {
        return ['status' => CommunityPostStatus::class, 'visibility' => CommunityPostVisibility::class, 'comments_enabled' => 'boolean', 'lock_version' => 'integer', 'locked_at' => 'immutable_datetime', 'edited_at' => 'immutable_datetime', 'published_at' => 'immutable_datetime', 'removed_at' => 'immutable_datetime'];
    }
}
