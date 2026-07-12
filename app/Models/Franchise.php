<?php

namespace App\Models;

use App\Concerns\HasEditorialRevisions;
use App\Concerns\HasModerationRestrictions;
use App\Enums\PublicationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\FranchiseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property PublicationStatus $status
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $archived_at
 * @property int $lock_version
 */
#[Fillable(['universe_id', 'name', 'slug', 'description', 'status', 'is_public', 'position', 'metadata', 'published_at', 'archived_at', 'lock_version', 'created_by', 'updated_by'])]
class Franchise extends Model
{
    /** @use HasFactory<FranchiseFactory> */
    use HasEditorialRevisions, HasFactory, HasModerationRestrictions;

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return HasMany<Work, $this> */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @param  Builder<Franchise>  $query
     * @return Builder<Franchise>
     */
    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published)
            ->where('is_public', true)
            ->whereNull('archived_at')
            ->whereHas('universe', fn (Builder $universe) => $universe
                ->where('status', PublicationStatus::Published)
                ->where('is_public', true))
            ->withoutActivePublicRestriction();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'is_public' => 'boolean',
            'metadata' => 'array',
            'published_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }
}
