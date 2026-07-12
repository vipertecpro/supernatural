<?php

namespace App\Models;

use App\Concerns\HasModerationRestrictions;
use App\Enums\MediaKind;
use App\Enums\MediaModerationStatus;
use App\Enums\MediaOrigin;
use App\Enums\MediaProcessingStatus;
use App\Enums\MediaStatus;
use App\Enums\MediaVisibility;
use Carbon\CarbonImmutable;
use Database\Factories\MediaAssetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $owner_user_id
 * @property int|null $universe_id
 * @property int|null $source_id
 * @property MediaKind $kind
 * @property MediaOrigin $origin
 * @property string $disk
 * @property string $storage_key
 * @property string $original_filename
 * @property string $display_filename
 * @property string $mime_type
 * @property string $extension
 * @property int $size_bytes
 * @property string $checksum
 * @property int|null $width
 * @property int|null $height
 * @property int|null $duration_seconds
 * @property string|null $alt_text
 * @property string|null $caption
 * @property string|null $attribution_text
 * @property string|null $copyright_owner
 * @property MediaStatus $status
 * @property MediaModerationStatus $moderation_status
 * @property MediaProcessingStatus $processing_status
 * @property MediaVisibility $visibility
 * @property int $lock_version
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $archived_at
 * @property CarbonImmutable|null $takedown_at
 * @property Collection<int, MediaVariant> $variants
 */
class MediaAsset extends Model
{
    /** @use HasFactory<MediaAssetFactory> */
    use HasFactory, HasModerationRestrictions, SoftDeletes;

    protected $fillable = ['owner_user_id', 'universe_id', 'source_id', 'content_license_id', 'kind', 'origin', 'disk', 'storage_key', 'original_filename', 'display_filename', 'mime_type', 'extension', 'size_bytes', 'checksum', 'width', 'height', 'duration_seconds', 'alt_text', 'caption', 'attribution_text', 'copyright_owner', 'status', 'moderation_status', 'processing_status', 'visibility', 'metadata', 'uploaded_at', 'published_at', 'archived_at', 'takedown_at', 'lock_version', 'created_by', 'updated_by'];

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return BelongsTo<Source, $this> */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /** @return BelongsTo<ContentLicense, $this> */
    public function contentLicense(): BelongsTo
    {
        return $this->belongsTo(ContentLicense::class);
    }

    /** @return HasMany<MediaVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    /** @return HasMany<MediaAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(MediaAttachment::class);
    }

    /** @return HasMany<MediaProcessingJob, $this> */
    public function processingJobs(): HasMany
    {
        return $this->hasMany(MediaProcessingJob::class);
    }

    /**
     * @param  Builder<MediaAsset>  $query
     * @return Builder<MediaAsset>
     */
    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->where('status', MediaStatus::Published)->where('moderation_status', MediaModerationStatus::Approved)->where('processing_status', MediaProcessingStatus::Ready)->where('visibility', MediaVisibility::Public)->whereNull('archived_at')->whereNull('takedown_at')->withoutActivePublicRestriction();
    }

    protected function casts(): array
    {
        return ['kind' => MediaKind::class, 'origin' => MediaOrigin::class, 'status' => MediaStatus::class, 'moderation_status' => MediaModerationStatus::class, 'processing_status' => MediaProcessingStatus::class, 'visibility' => MediaVisibility::class, 'metadata' => 'array', 'uploaded_at' => 'immutable_datetime', 'published_at' => 'immutable_datetime', 'archived_at' => 'immutable_datetime', 'takedown_at' => 'immutable_datetime', 'lock_version' => 'integer'];
    }
}
