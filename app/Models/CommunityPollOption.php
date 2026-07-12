<?php

namespace App\Models;

use Database\Factories\CommunityPollOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property int $id @property int $poll_id @property string $text @property int $position */
class CommunityPollOption extends Model
{
    /** @use HasFactory<CommunityPollOptionFactory> */
    use HasFactory;

    protected $table = 'poll_options';

    protected $fillable = ['poll_id', 'text', 'position'];

    /** @return BelongsTo<CommunityPoll, $this> */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(CommunityPoll::class, 'poll_id');
    }

    /** @return HasMany<CommunityPollVote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(CommunityPollVote::class, 'poll_option_id');
    }

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }
}
