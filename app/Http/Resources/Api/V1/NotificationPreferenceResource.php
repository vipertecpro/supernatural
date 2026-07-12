<?php

namespace App\Http\Resources\Api\V1;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NotificationPreference */
class NotificationPreferenceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['type' => $this->type, 'channel' => $this->channel->value, 'state' => $this->state->value, 'lock_version' => $this->lock_version, 'updated_at' => $this->updated_at?->toISOString()];
    }
}
