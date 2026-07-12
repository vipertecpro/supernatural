<?php

namespace App\Models;

use App\Enums\MediaProcessingStatus;
use Database\Factories\MediaProcessingJobFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $media_asset_id
 * @property string $operation
 * @property MediaProcessingStatus $status
 */
class MediaProcessingJob extends Model
{
    /** @use HasFactory<MediaProcessingJobFactory> */
    use HasFactory;

    protected $fillable = ['media_asset_id', 'operation', 'status', 'attempts', 'error_code', 'safe_metadata', 'started_at', 'completed_at'];

    /** @return BelongsTo<MediaAsset, $this> */
    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    protected function casts(): array
    {
        return ['status' => MediaProcessingStatus::class, 'safe_metadata' => 'array', 'started_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime'];
    }
}
