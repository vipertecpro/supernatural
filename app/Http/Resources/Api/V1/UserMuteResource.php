<?php

namespace App\Http\Resources\Api\V1;

use App\Models\UserMute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserMute */
class UserMuteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'muted_user' => ['id' => $this->muted_user_id, 'name' => $this->mutedUser->name], 'scope' => $this->scope->value, 'expires_at' => $this->expires_at?->toISOString(), 'created_at' => $this->created_at?->toISOString()];
    }
}
