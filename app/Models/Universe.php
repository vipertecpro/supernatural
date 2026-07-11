<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use Database\Factories\UniverseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property PublicationStatus $status */
#[Fillable(['name', 'slug', 'description', 'status', 'is_public', 'metadata', 'created_by', 'updated_by'])]
class Universe extends Model
{
    /** @use HasFactory<UniverseFactory> */
    use HasFactory;

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

    /** @return HasMany<Source, $this> */
    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }

    /** @return HasMany<Franchise, $this> */
    public function franchises(): HasMany
    {
        return $this->hasMany(Franchise::class);
    }

    /** @return HasMany<Work, $this> */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    /** @return HasMany<SpoilerConstraint, $this> */
    public function spoilerConstraints(): HasMany
    {
        return $this->hasMany(SpoilerConstraint::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'is_public' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
