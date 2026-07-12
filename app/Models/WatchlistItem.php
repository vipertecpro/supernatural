<?php

namespace App\Models;

use Database\Factories\WatchlistItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $watchlist_id
 * @property string $target_type
 * @property int $target_id
 * @property int $position
 * @property mixed $added_at
 * @property string|null $private_note
 */
#[Fillable(['watchlist_id', 'target_type', 'target_id', 'position', 'added_at', 'private_note'])]
class WatchlistItem extends Model
{
    /** @use HasFactory<WatchlistItemFactory> */
    use HasFactory;

    /** @return BelongsTo<Watchlist, $this> */
    public function watchlist(): BelongsTo
    {
        return $this->belongsTo(Watchlist::class);
    }

    /** @return MorphTo<Model, $this> */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['added_at' => 'immutable_datetime', 'position' => 'integer'];
    }
}
