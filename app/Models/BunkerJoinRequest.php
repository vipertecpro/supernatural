<?php

namespace App\Models;

use App\Enums\BunkerJoinRequestStatus;
use Database\Factories\BunkerJoinRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $bunker_id
 * @property int|null $user_id
 * @property BunkerJoinRequestStatus $status
 * @property Bunker $bunker
 */
class BunkerJoinRequest extends Model
{
    /** @use HasFactory<BunkerJoinRequestFactory> */
    use HasFactory;

    protected $fillable = ['bunker_id', 'user_id', 'status', 'active_key', 'message', 'reviewed_by', 'decision_explanation', 'submitted_at', 'reviewed_at'];

    /** @return BelongsTo<Bunker, $this> */
    public function bunker(): BelongsTo
    {
        return $this->belongsTo(Bunker::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['status' => BunkerJoinRequestStatus::class, 'submitted_at' => 'immutable_datetime', 'reviewed_at' => 'immutable_datetime'];
    }
}
