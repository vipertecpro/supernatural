<?php

namespace App\Models;

use Database\Factories\CommunityPollVoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property int $id @property int $poll_id @property int $poll_option_id @property int|null $user_id */
class CommunityPollVote extends Model
{
    /** @use HasFactory<CommunityPollVoteFactory> */
    use HasFactory;

    protected $table = 'poll_votes';

    protected $fillable = ['poll_id', 'poll_option_id', 'user_id'];

    /** @return BelongsTo<CommunityPoll, $this> */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(CommunityPoll::class, 'poll_id');
    }

    /** @return BelongsTo<CommunityPollOption, $this> */
    public function option(): BelongsTo
    {
        return $this->belongsTo(CommunityPollOption::class, 'poll_option_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
