<?php

namespace Database\Factories;

use App\Enums\MediaProcessingStatus;
use App\Models\MediaAsset;
use App\Models\MediaProcessingJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MediaProcessingJob> */
class MediaProcessingJobFactory extends Factory
{
    public function definition(): array
    {
        return ['media_asset_id' => MediaAsset::factory(), 'operation' => 'validate', 'status' => MediaProcessingStatus::Pending, 'attempts' => 0, 'error_code' => null, 'safe_metadata' => [], 'started_at' => null, 'completed_at' => null];
    }
}
