<?php

namespace App\Models;

use App\Enums\PersonalVisibility;
use Database\Factories\WatchlistFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $universe_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property PersonalVisibility $visibility
 * @property bool $is_default
 * @property int $position
 * @property int $lock_version
 */
#[Fillable(['user_id', 'universe_id', 'name', 'slug', 'description', 'visibility', 'is_default', 'default_key', 'position', 'lock_version'])]
class Watchlist extends Model
{
    /** @use HasFactory<WatchlistFactory> */
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

    /** @return HasMany<WatchlistItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(WatchlistItem::class)->orderBy('position')->orderBy('id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['visibility' => PersonalVisibility::class, 'is_default' => 'boolean', 'position' => 'integer', 'lock_version' => 'integer'];
    }
}
