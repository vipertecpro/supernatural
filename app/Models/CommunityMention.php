<?php

namespace App\Models;

use App\Enums\CommunityMentionType;
use Database\Factories\CommunityMentionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** @property int $id @property string $mentionable_type @property int $mentionable_id @property int|null $mentioned_user_id @property CommunityMentionType $type */
class CommunityMention extends Model
{
    /** @use HasFactory<CommunityMentionFactory> */
    use HasFactory;

    protected $table = 'mentions';

    protected $fillable = ['mentionable_type', 'mentionable_id', 'mentioned_user_id', 'mentioning_user_id', 'type', 'notification_key', 'inactive_at'];

    /** @return MorphTo<Model, $this> */
    public function mentionable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }

    protected function casts(): array
    {
        return ['type' => CommunityMentionType::class, 'inactive_at' => 'immutable_datetime'];
    }
}
