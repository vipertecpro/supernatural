<?php

namespace App\Actions\Authorization;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;

class RemoveRole
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * Remove a role and audit the change.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function handle(User $user, Role|RoleName|string $role, ?User $actor = null, array $metadata = []): bool
    {
        $roleModel = $this->resolveRole($role);

        if ($user->roles()->detach($roleModel->getKey()) === 0) {
            return false;
        }

        $this->auditLogger->record(
            event: 'authorization.role_removed',
            auditable: $user,
            metadata: ['role' => $roleModel->name->value, ...$metadata],
            actor: $actor,
        );

        return true;
    }

    /** Resolve a role input to a persisted role. */
    private function resolveRole(Role|RoleName|string $role): Role
    {
        if ($role instanceof Role) {
            return $role;
        }

        $name = $role instanceof RoleName ? $role->value : $role;

        return Role::query()->where('name', $name)->firstOrFail();
    }
}
