<?php

namespace Database\Factories;

use App\Enums\MediaProcessingStatus;
use App\Enums\MediaVariantPurpose;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<MediaVariant> */
class MediaVariantFactory extends Factory
{
    public function definition(): array
    {
        $key = (string) Str::uuid();

        return ['media_asset_id' => MediaAsset::factory(), 'purpose' => MediaVariantPurpose::Thumbnail, 'format' => 'webp', 'disk' => 'local', 'storage_key' => "media/variants/{$key}.webp", 'mime_type' => 'image/webp', 'size_bytes' => 128, 'checksum' => hash('sha256', $key), 'width' => 128, 'height' => 128, 'processing_status' => MediaProcessingStatus::Ready, 'ready_at' => now()];
    }
}
