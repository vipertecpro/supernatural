<?php

namespace App\Models;

use App\Enums\PersonalVisibility;
use Database\Factories\UserFandomPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property int $id @property int $user_id @property int $universe_id @property int|null $preferred_viewing_order_id @property string $default_locale @property bool $auto_complete_progress @property bool $auto_remove_completed_watchlist_items @property PersonalVisibility $continue_watching_visibility @property PersonalVisibility $rating_visibility @property PersonalVisibility $favourite_visibility @property PersonalVisibility $journey_visibility @property int $lock_version */
#[Fillable(['user_id', 'universe_id', 'preferred_viewing_order_id', 'default_locale', 'auto_complete_progress', 'auto_remove_completed_watchlist_items', 'continue_watching_visibility', 'rating_visibility', 'favourite_visibility', 'journey_visibility', 'lock_version'])]
class UserFandomPreference extends Model
{
    /** @use HasFactory<UserFandomPreferenceFactory> */
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
    public function preferredViewingOrder(): BelongsTo
    {
        return $this->belongsTo(ViewingOrder::class, 'preferred_viewing_order_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['auto_complete_progress' => 'boolean', 'auto_remove_completed_watchlist_items' => 'boolean', 'continue_watching_visibility' => PersonalVisibility::class, 'rating_visibility' => PersonalVisibility::class, 'favourite_visibility' => PersonalVisibility::class, 'journey_visibility' => PersonalVisibility::class, 'lock_version' => 'integer'];
    }
}
