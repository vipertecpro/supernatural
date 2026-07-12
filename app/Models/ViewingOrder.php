<?php

namespace App\Models;

use App\Enums\PersonalVisibility;
use App\Enums\PublicationStatus;
use App\Enums\ViewingOrderType;
use Database\Factories\ViewingOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $universe_id
 * @property int|null $franchise_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property ViewingOrderType $type
 * @property PublicationStatus $status
 * @property PersonalVisibility $visibility
 * @property bool $is_default
 * @property string|null $locale
 * @property mixed $published_at
 * @property mixed $archived_at
 * @property int $lock_version
 */
#[Fillable(['universe_id', 'franchise_id', 'name', 'slug', 'description', 'type', 'status', 'visibility', 'is_default', 'default_key', 'locale', 'created_by', 'updated_by', 'published_at', 'archived_at', 'lock_version'])]
class ViewingOrder extends Model
{
    /** @use HasFactory<ViewingOrderFactory> */
    use HasFactory;

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return BelongsTo<Franchise, $this> */
    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    /** @return HasMany<ViewingOrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ViewingOrderItem::class)->orderBy('position')->orderBy('id');
    }

    /**
     * @param  Builder<ViewingOrder>  $query
     * @return Builder<ViewingOrder>
     */
    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published)
            ->where('visibility', PersonalVisibility::Public)
            ->whereNull('archived_at')
            ->whereHas('universe', fn (Builder $universe) => $universe
                ->where('status', PublicationStatus::Published)
                ->where('is_public', true));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => ViewingOrderType::class,
            'status' => PublicationStatus::class,
            'visibility' => PersonalVisibility::class,
            'is_default' => 'boolean',
            'published_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }
}
