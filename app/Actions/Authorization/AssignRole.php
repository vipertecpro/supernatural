<?php

namespace App\Actions\Authorization;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;

class AssignRole
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * Assign a role once and audit the change.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function handle(User $user, Role|RoleName|string $role, ?User $actor = null, array $metadata = []): bool
    {
        $roleModel = $this->resolveRole($role);
        $changes = $user->roles()->syncWithoutDetaching([$roleModel->getKey()]);

        if ($changes['attached'] === []) {
            return false;
        }

        $this->auditLogger->record(
            event: 'authorization.role_assigned',
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
