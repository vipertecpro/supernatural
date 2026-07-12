<?php

namespace App\Models;

use App\Enums\SpoilerTolerance;
use Database\Factories\UserSpoilerPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property int $universe_id
 * @property SpoilerTolerance $tolerance
 * @property bool $show_warnings
 * @property string $rewatch_behavior
 * @property int $lock_version
 */
#[Fillable(['user_id', 'universe_id', 'tolerance', 'show_warnings', 'rewatch_behavior', 'lock_version'])]
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
        return ['tolerance' => SpoilerTolerance::class, 'show_warnings' => 'boolean', 'lock_version' => 'integer'];
    }
}
