<?php

namespace App\Models;

use App\Enums\BunkerMembershipRole;
use App\Enums\BunkerMembershipStatus;
use Carbon\CarbonImmutable;
use Database\Factories\BunkerMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $bunker_id
 * @property int|null $user_id
 * @property BunkerMembershipRole $role
 * @property BunkerMembershipStatus $status
 * @property int $lock_version
 * @property Bunker $bunker
 * @property CarbonImmutable|null $joined_at
 */
class BunkerMembership extends Model
{
    /** @use HasFactory<BunkerMembershipFactory> */
    use HasFactory;

    protected $fillable = ['bunker_id', 'user_id', 'role', 'status', 'approved_by', 'active_key', 'lock_version', 'joined_at', 'left_at', 'removed_at'];

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
        return ['role' => BunkerMembershipRole::class, 'status' => BunkerMembershipStatus::class, 'lock_version' => 'integer', 'joined_at' => 'immutable_datetime', 'left_at' => 'immutable_datetime', 'removed_at' => 'immutable_datetime'];
    }
}
