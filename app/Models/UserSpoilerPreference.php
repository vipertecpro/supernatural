<?php

namespace App\Models;

use App\Enums\SpoilerTolerance;
use Database\Factories\UserSpoilerPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property SpoilerTolerance $tolerance */
#[Fillable(['user_id', 'universe_id', 'tolerance', 'show_warnings'])]
class UserSpoilerPreference extends Model
{
    /** @use HasFactory<UserSpoilerPreferenceFactory> */
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

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['tolerance' => SpoilerTolerance::class, 'show_warnings' => 'boolean'];
    }
}
