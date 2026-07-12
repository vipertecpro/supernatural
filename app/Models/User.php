<?php

namespace App\Models;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the roles assigned to the user.
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /** @return HasMany<UserBlock, $this> */
    public function blocks(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocker_user_id');
    }

    /** @return HasMany<UserMute, $this> */
    public function mutes(): HasMany
    {
        return $this->hasMany(UserMute::class, 'muting_user_id');
    }

    /**
     * Determine whether the user has a role.
     */
    public function hasRole(RoleName|string $role): bool
    {
        $roleName = $role instanceof RoleName ? $role->value : $role;

        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Determine whether the user has a permission through an assigned role.
     */
    public function hasPermission(PermissionName|string $permission): bool
    {
        $permissionName = $permission instanceof PermissionName ? $permission->value : $permission;

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('name', $permissionName))
            ->exists();
    }

    /**
     * Get the unique permissions granted through all assigned roles.
     *
     * @return Collection<int, Permission>
     */
    public function grantedPermissions(): Collection
    {
        $this->loadMissing('roles.permissions');

        return $this->roles
            ->flatMap(fn (Role $role) => $role->permissions)
            ->unique('id')
            ->values();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $user): void {
            UserBlock::query()->where('blocker_user_id', $user->id)->orWhere('blocked_user_id', $user->id)->delete();
            UserMute::query()->where('muting_user_id', $user->id)->orWhere('muted_user_id', $user->id)->delete();
            Bunker::query()->where('owner_user_id', $user->id)->whereNotIn('status', ['archived'])->update(['status' => 'archived', 'archived_at' => now(), 'owner_membership_key' => null]);
            BunkerMembership::query()->where('user_id', $user->id)->where('status', 'active')->update(['status' => 'left', 'active_key' => null, 'left_at' => now()]);
            BunkerJoinRequest::query()->where('user_id', $user->id)->where('status', 'pending')->update(['status' => 'withdrawn', 'active_key' => null]);
            BunkerInvitation::query()->where('invited_user_id', $user->id)->where('status', 'pending')->update(['status' => 'revoked', 'active_key' => null, 'revoked_at' => now()]);
            CommunityMention::query()->where('mentioned_user_id', $user->id)->update(['inactive_at' => now(), 'notification_key' => null]);
            $restrictionIds = UserRestriction::query()->where('user_id', $user->id)->pluck('id');
            if ($restrictionIds->isNotEmpty()) {
                Appeal::query()->whereIn('user_restriction_id', $restrictionIds)->update(['user_restriction_id' => null]);
            }
        });
    }
}
