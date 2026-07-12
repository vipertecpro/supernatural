<?php

namespace App\Models;

use App\Enums\MediaProcessingStatus;
use App\Enums\MediaVariantPurpose;
use Database\Factories\MediaVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $media_asset_id
 * @property MediaVariantPurpose $purpose
 * @property string $format
 * @property string $mime_type
 * @property int $size_bytes
 * @property int|null $width
 * @property int|null $height
 * @property MediaProcessingStatus $processing_status
 */
class MediaVariant extends Model
{
    /** @use HasFactory<MediaVariantFactory> */
    use HasFactory;

    protected $fillable = ['media_asset_id', 'purpose', 'format', 'disk', 'storage_key', 'mime_type', 'size_bytes', 'checksum', 'width', 'height', 'processing_status', 'ready_at'];

    /** @return BelongsTo<MediaAsset, $this> */
    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    protected function casts(): array
    {
        return ['purpose' => MediaVariantPurpose::class, 'processing_status' => MediaProcessingStatus::class, 'ready_at' => 'immutable_datetime'];
    }
}
