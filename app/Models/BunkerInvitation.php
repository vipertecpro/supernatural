<?php

namespace App\Models;

use App\Enums\BunkerInvitationStatus;
use App\Enums\BunkerMembershipRole;
use Database\Factories\BunkerInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $bunker_id
 * @property int|null $invited_user_id
 * @property int|null $inviter_user_id
 * @property BunkerMembershipRole $proposed_role
 * @property BunkerInvitationStatus $status
 * @property string $token_hash
 * @property Carbon $expires_at
 * @property Bunker $bunker
 */
class BunkerInvitation extends Model
{
    /** @use HasFactory<BunkerInvitationFactory> */
    use HasFactory;

    protected $fillable = ['bunker_id', 'invited_user_id', 'inviter_user_id', 'proposed_role', 'token_hash', 'status', 'active_key', 'sent_at', 'expires_at', 'accepted_at', 'declined_at', 'revoked_at'];

    /** @return BelongsTo<Bunker, $this> */
    public function bunker(): BelongsTo
    {
        return $this->belongsTo(Bunker::class);
    }

    /** @return BelongsTo<User, $this> */
    public function invitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    protected function casts(): array
    {
        return ['proposed_role' => BunkerMembershipRole::class, 'status' => BunkerInvitationStatus::class, 'sent_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime', 'accepted_at' => 'immutable_datetime', 'declined_at' => 'immutable_datetime', 'revoked_at' => 'immutable_datetime'];
    }
}
