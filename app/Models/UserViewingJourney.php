<?php

namespace App\Models;

use App\Enums\JourneyStatus;
use App\Enums\PersonalVisibility;
use Database\Factories\UserViewingJourneyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $universe_id
 * @property int $viewing_order_id
 * @property int|null $rewatch_cycle_id
 * @property JourneyStatus $status
 * @property int|null $current_item_id
 * @property int|null $current_work_id
 * @property int|null $current_season_id
 * @property int|null $current_episode_id
 * @property PersonalVisibility $visibility
 * @property mixed $started_at
 * @property mixed $paused_at
 * @property mixed $completed_at
 * @property mixed $abandoned_at
 * @property int $lock_version
 */
#[Fillable(['user_id', 'universe_id', 'viewing_order_id', 'rewatch_cycle_id', 'status', 'active_key', 'current_item_id', 'current_work_id', 'current_season_id', 'current_episode_id', 'visibility', 'started_at', 'paused_at', 'completed_at', 'abandoned_at', 'lock_version'])]
class UserViewingJourney extends Model
{
    /** @use HasFactory<UserViewingJourneyFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return BelongsTo<ViewingOrder, $this> */
    public function viewingOrder(): BelongsTo
    {
        return $this->belongsTo(ViewingOrder::class);
    }

    /** @return BelongsTo<RewatchCycle, $this> */
    public function rewatchCycle(): BelongsTo
    {
        return $this->belongsTo(RewatchCycle::class);
    }

    /** @return BelongsTo<ViewingOrderItem, $this> */
    public function currentItem(): BelongsTo
    {
        return $this->belongsTo(ViewingOrderItem::class, 'current_item_id');
    }

    /** @return HasMany<ViewingProgress, $this> */
    public function progress(): HasMany
    {
        return $this->hasMany(ViewingProgress::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => JourneyStatus::class, 'visibility' => PersonalVisibility::class, 'started_at' => 'immutable_datetime', 'paused_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime', 'abandoned_at' => 'immutable_datetime', 'lock_version' => 'integer'];
    }
}
