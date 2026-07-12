<?php

namespace App\Http\Resources\Api\V1;

use App\Models\UserBlock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserBlock */
class UserBlockResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'blocked_user' => ['id' => $this->blocked_user_id, 'name' => $this->blocked->name], 'reason_code' => $this->reason_code, 'created_at' => $this->created_at?->toISOString()];
    }
}
