<?php

namespace App\Models;

use Database\Factories\FavouriteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** @property int $id @property int $user_id @property int $universe_id @property string $target_type @property int $target_id */
#[Fillable(['user_id', 'universe_id', 'target_type', 'target_id'])]
class Favourite extends Model
{
    /** @use HasFactory<FavouriteFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphTo<Model, $this> */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
