<?php

namespace App\Models;

use App\Concerns\HasModerationRestrictions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\BunkerStatus;
use App\Enums\BunkerVisibility;
use App\Enums\SpoilerSeverity;
use Carbon\CarbonImmutable;
use Database\Factories\BunkerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $universe_id
 * @property int|null $owner_user_id
 * @property string $name
 * @property string $slug
 * @property BunkerVisibility $visibility
 * @property BunkerStatus $status
 * @property SpoilerSeverity|null $spoiler_severity
 * @property bool $requires_approval
 * @property bool $requires_invitation
 * @property int|null $owner_membership_key
 * @property int $lock_version
 * @property User|null $owner
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $archived_at
 */
class Bunker extends Model
{
    /** @use HasFactory<BunkerFactory> */
    use HasFactory, HasModerationRestrictions, HasSpoilerConstraints, SoftDeletes;

    protected $fillable = ['universe_id', 'owner_user_id', 'name', 'slug', 'description', 'rules_summary', 'visibility', 'status', 'requires_approval', 'requires_invitation', 'default_locale', 'spoiler_severity', 'owner_membership_key', 'lock_version', 'published_at', 'archived_at', 'restricted_at'];

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return BelongsToMany<BunkerCategory, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BunkerCategory::class, 'bunker_category')->withTimestamps();
    }

    /** @return HasMany<BunkerMembership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(BunkerMembership::class);
    }

    /** @return HasMany<CommunityPost, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    /** @return HasMany<BunkerRule, $this> */
    public function rules(): HasMany
    {
        return $this->hasMany(BunkerRule::class);
    }

    protected function casts(): array
    {
        return ['visibility' => BunkerVisibility::class, 'status' => BunkerStatus::class, 'spoiler_severity' => SpoilerSeverity::class, 'requires_approval' => 'boolean', 'requires_invitation' => 'boolean', 'lock_version' => 'integer', 'published_at' => 'immutable_datetime', 'archived_at' => 'immutable_datetime', 'restricted_at' => 'immutable_datetime'];
    }
}
