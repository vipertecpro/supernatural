<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Notifications\Services\NotificationRenderer;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserNotification */
class UserNotificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $rendered = app(NotificationRenderer::class)->render($this->resource);

        return ['id' => $this->id, 'type' => $this->type, 'schema_version' => $this->schema_version, 'priority' => $this->priority->value, 'status' => $this->status->value, 'rendered' => $rendered, 'read_at' => $this->read_at?->toISOString(), 'archived_at' => $this->archived_at?->toISOString(), 'expires_at' => $this->expires_at?->toISOString(), 'created_at' => $this->created_at?->toISOString()];
    }
}
