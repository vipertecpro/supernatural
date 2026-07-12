<?php

namespace Database\Factories;

use App\Enums\ExternalMediaProvider;
use App\Enums\MediaKind;
use App\Enums\MediaModerationStatus;
use App\Enums\MediaStatus;
use App\Enums\MediaVisibility;
use App\Models\ExternalEmbed;
use App\Models\Source;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ExternalEmbed> */
class ExternalEmbedFactory extends Factory
{
    public function definition(): array
    {
        $id = fake()->unique()->bothify('vid########');

        return ['owner_user_id' => User::factory(), 'universe_id' => null, 'source_id' => Source::factory(), 'content_license_id' => null, 'provider' => ExternalMediaProvider::YouTube, 'provider_content_id' => $id, 'canonical_url' => "https://www.youtube.com/{$id}", 'embed_url' => "https://www.youtube-nocookie.com/embed/{$id}", 'kind' => MediaKind::Video, 'title' => 'Authorized example video', 'description' => 'Provider-authorized fictional fixture.', 'thumbnail_url' => null, 'creator' => 'Example Creator', 'publisher' => 'Example Publisher', 'attribution_text' => 'Example attribution', 'status' => MediaStatus::Pending, 'moderation_status' => MediaModerationStatus::Pending, 'visibility' => MediaVisibility::Private, 'provider_metadata' => [], 'published_at' => null, 'archived_at' => null, 'takedown_at' => null, 'lock_version' => 0, 'created_by' => null, 'updated_by' => null];
    }
}
