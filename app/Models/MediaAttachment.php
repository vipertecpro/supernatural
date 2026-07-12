<?php

namespace App\Models;

use App\Concerns\HasModerationRestrictions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\MediaAttachmentRole;
use App\Enums\PublicationStatus;
use Database\Factories\MediaAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $media_asset_id
 * @property int|null $external_embed_id
 * @property string $attachable_type
 * @property int $attachable_id
 * @property MediaAttachmentRole $role
 * @property int $position
 * @property bool $is_primary
 * @property string|null $locale
 * @property string|null $caption_override
 * @property string|null $alt_text_override
 * @property PublicationStatus $status
 * @property int $lock_version
 * @property MediaAsset|null $mediaAsset
 * @property ExternalEmbed|null $externalEmbed
 */
class MediaAttachment extends Model
{
    /** @use HasFactory<MediaAttachmentFactory> */
    use HasFactory, HasModerationRestrictions, HasSpoilerConstraints, SoftDeletes;

    protected $fillable = ['media_asset_id', 'external_embed_id', 'attachable_type', 'attachable_id', 'role', 'position', 'is_primary', 'primary_key', 'locale', 'caption_override', 'alt_text_override', 'status', 'spoiler_constraint_id', 'lock_version', 'created_by', 'updated_by'];

    /** @return BelongsTo<MediaAsset, $this> */
    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    /** @return BelongsTo<ExternalEmbed, $this> */
    public function externalEmbed(): BelongsTo
    {
        return $this->belongsTo(ExternalEmbed::class);
    }

    /** @return MorphTo<Model, $this> */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<SpoilerConstraint, $this> */
    public function spoilerConstraint(): BelongsTo
    {
        return $this->belongsTo(SpoilerConstraint::class);
    }

    protected function casts(): array
    {
        return ['role' => MediaAttachmentRole::class, 'is_primary' => 'boolean', 'status' => PublicationStatus::class, 'lock_version' => 'integer'];
    }
}
