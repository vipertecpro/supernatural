<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = collect(PermissionName::cases())
            ->mapWithKeys(function (PermissionName $permission): array {
                $model = Permission::query()->updateOrCreate(
                    ['name' => $permission->value],
                    ['label' => $permission->label()],
                );

                return [$permission->value => $model];
            });

        $rolePermissions = [
            RoleName::Fan->value => [PermissionName::DashboardAccess],
            RoleName::Contributor->value => [
                PermissionName::DashboardAccess,
                PermissionName::ContentContribute,
                PermissionName::CatalogViewDrafts,
                PermissionName::CatalogCreate,
                PermissionName::CatalogUpdate,
            ],
            RoleName::Moderator->value => [
                PermissionName::DashboardAccess,
                PermissionName::CommunityModerate,
                PermissionName::UsersModerate,
                PermissionName::AuditLogsView,
            ],
            RoleName::Administrator->value => PermissionName::cases(),
        ];

        foreach (RoleName::cases() as $roleName) {
            $role = Role::query()->updateOrCreate(
                ['name' => $roleName->value],
                ['label' => $roleName->label()],
            );

            $permissionIds = collect($rolePermissions[$roleName->value])
                ->map(fn (PermissionName $permission): int => $permissions->get($permission->value)->id)
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}
