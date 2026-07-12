<?php

namespace Database\Factories;

use App\Enums\MediaAttachmentRole;
use App\Enums\PublicationStatus;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MediaAttachment> */
class MediaAttachmentFactory extends Factory
{
    public function definition(): array
    {
        return ['media_asset_id' => MediaAsset::factory(), 'external_embed_id' => null, 'attachable_type' => 'work', 'attachable_id' => Work::factory(), 'role' => MediaAttachmentRole::Gallery, 'position' => 0, 'is_primary' => false, 'primary_key' => null, 'locale' => 'en', 'caption_override' => null, 'alt_text_override' => null, 'status' => PublicationStatus::Draft, 'spoiler_constraint_id' => null, 'lock_version' => 0, 'created_by' => null, 'updated_by' => null];
    }
}
