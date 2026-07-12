<?php

namespace App\Models;

use App\Concerns\HasModerationRestrictions;
use App\Enums\CommunityPollResultsVisibility;
use App\Enums\CommunityPollStatus;
use App\Enums\CommunityPollType;
use Database\Factories\CommunityPollFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $post_id
 * @property CommunityPollType $type
 * @property CommunityPollStatus $status
 * @property CommunityPollResultsVisibility $results_visibility
 * @property int $maximum_choices
 * @property int $lock_version
 * @property Carbon|null $closes_at
 * @property Carbon|null $closed_at
 * @property CommunityPost $post
 */
class CommunityPoll extends Model
{
    /** @use HasFactory<CommunityPollFactory> */
    use HasFactory, HasModerationRestrictions;

    protected $table = 'polls';

    protected $fillable = ['post_id', 'question', 'type', 'maximum_choices', 'status', 'results_visibility', 'lock_version', 'opens_at', 'closes_at', 'closed_at'];

    /** @return BelongsTo<CommunityPost, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    /** @return HasMany<CommunityPollOption, $this> */
    public function options(): HasMany
    {
        return $this->hasMany(CommunityPollOption::class, 'poll_id');
    }

    /** @return HasMany<CommunityPollVote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(CommunityPollVote::class, 'poll_id');
    }

    protected function casts(): array
    {
        return ['type' => CommunityPollType::class, 'status' => CommunityPollStatus::class, 'results_visibility' => CommunityPollResultsVisibility::class, 'maximum_choices' => 'integer', 'lock_version' => 'integer', 'opens_at' => 'immutable_datetime', 'closes_at' => 'immutable_datetime', 'closed_at' => 'immutable_datetime'];
    }
}
