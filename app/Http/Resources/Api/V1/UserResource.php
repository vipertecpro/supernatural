<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => $this->hasVerifiedEmail(),
            'roles' => $this->roles
                ->map(fn (Role $role): string => $role->name->value)
                ->sort()
                ->values()
                ->all(),
            'permissions' => $this->grantedPermissions()
                ->map(fn (Permission $permission): string => $permission->name->value)
                ->sort()
                ->values()
                ->all(),
        ];
    }

    /**
     * Add API version and correlation metadata to the resource response.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return ['meta' => ApiResponse::meta($request)];
    }
}
