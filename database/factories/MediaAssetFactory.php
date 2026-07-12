<?php

namespace Database\Factories;

use App\Enums\MediaKind;
use App\Enums\MediaModerationStatus;
use App\Enums\MediaOrigin;
use App\Enums\MediaProcessingStatus;
use App\Enums\MediaStatus;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<MediaAsset> */
class MediaAssetFactory extends Factory
{
    public function definition(): array
    {
        $key = (string) Str::uuid();

        return ['owner_user_id' => User::factory(), 'universe_id' => null, 'source_id' => null, 'content_license_id' => null, 'kind' => MediaKind::Image, 'origin' => MediaOrigin::ProjectOriginal, 'disk' => 'local', 'storage_key' => "media/quarantine/{$key}.png", 'original_filename' => 'original.png', 'display_filename' => 'original.png', 'mime_type' => 'image/png', 'extension' => 'png', 'size_bytes' => 256, 'checksum' => hash('sha256', $key), 'width' => 64, 'height' => 64, 'duration_seconds' => null, 'alt_text' => 'Original geometric placeholder', 'caption' => null, 'attribution_text' => 'Original project fixture', 'copyright_owner' => 'Example Project', 'status' => MediaStatus::Pending, 'moderation_status' => MediaModerationStatus::Pending, 'processing_status' => MediaProcessingStatus::Ready, 'visibility' => MediaVisibility::Private, 'metadata' => [], 'uploaded_at' => now(), 'published_at' => null, 'archived_at' => null, 'takedown_at' => null, 'lock_version' => 0, 'created_by' => null, 'updated_by' => null];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['status' => MediaStatus::Published, 'moderation_status' => MediaModerationStatus::Approved, 'processing_status' => MediaProcessingStatus::Ready, 'visibility' => MediaVisibility::Public, 'published_at' => now()]);
    }
}
