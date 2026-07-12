<?php

namespace App\Http\Resources\Api\V1;

use App\Models\UserFandomPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserFandomPreference */
class JourneyPreferenceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'universe_id' => $this->universe_id, 'preferred_viewing_order_id' => $this->preferred_viewing_order_id, 'default_locale' => $this->default_locale, 'auto_complete_progress' => $this->auto_complete_progress, 'auto_remove_completed_watchlist_items' => $this->auto_remove_completed_watchlist_items, 'continue_watching_visibility' => 'private', 'rating_visibility' => 'private', 'favourite_visibility' => 'private', 'journey_visibility' => 'private', 'lock_version' => $this->lock_version];
    }
}
