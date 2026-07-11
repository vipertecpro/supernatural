<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Return the authenticated client's safe identity and authorization data.
     */
    public function __invoke(Request $request): UserResource
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        $user->loadMissing('roles.permissions');

        return new UserResource($user);
    }
}
