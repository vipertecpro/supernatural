<?php

namespace App\Models;

use App\Enums\BunkerBanStatus;
use Database\Factories\BunkerBanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $bunker_id
 * @property int|null $user_id
 * @property BunkerBanStatus $status
 * @property Bunker $bunker
 */
class BunkerBan extends Model
{
    /** @use HasFactory<BunkerBanFactory> */
    use HasFactory;

    protected $fillable = ['bunker_id', 'user_id', 'issued_by', 'lifted_by', 'reason_code', 'user_visible_explanation', 'private_note', 'status', 'active_key', 'effective_at', 'expires_at', 'lifted_at'];

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
        return ['status' => BunkerBanStatus::class, 'effective_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime', 'lifted_at' => 'immutable_datetime'];
    }
}
