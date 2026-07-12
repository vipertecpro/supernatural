<?php

namespace App\Models;

use App\Enums\PersonalVisibility;
use App\Enums\RewatchStatus;
use Database\Factories\RewatchCycleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $universe_id
 * @property int|null $work_id
 * @property int|null $viewing_order_id
 * @property int $cycle_number
 * @property RewatchStatus $status
 * @property PersonalVisibility $visibility
 * @property mixed $started_at
 * @property mixed $completed_at
 * @property mixed $abandoned_at
 */
#[Fillable(['user_id', 'universe_id', 'work_id', 'viewing_order_id', 'cycle_number', 'status', 'active_key', 'visibility', 'started_at', 'completed_at', 'abandoned_at'])]
class RewatchCycle extends Model
{
    /** @use HasFactory<RewatchCycleFactory> */
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

    /** @return BelongsTo<Work, $this> */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /** @return BelongsTo<ViewingOrder, $this> */
    public function viewingOrder(): BelongsTo
    {
        return $this->belongsTo(ViewingOrder::class);
    }

    /** @return HasMany<ViewingProgress, $this> */
    public function progress(): HasMany
    {
        return $this->hasMany(ViewingProgress::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RewatchStatus::class, 'visibility' => PersonalVisibility::class, 'started_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime', 'abandoned_at' => 'immutable_datetime', 'cycle_number' => 'integer'];
    }
}
