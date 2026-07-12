<?php

namespace App\Models;

use App\Enums\ExternalMediaProvider;
use App\Enums\MediaKind;
use App\Enums\MediaModerationStatus;
use App\Enums\MediaStatus;
use App\Enums\MediaVisibility;
use Carbon\CarbonImmutable;
use Database\Factories\ExternalEmbedFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $owner_user_id
 * @property int|null $universe_id
 * @property int $source_id
 * @property ExternalMediaProvider $provider
 * @property string $provider_content_id
 * @property string $canonical_url
 * @property string $embed_url
 * @property MediaKind $kind
 * @property string $title
 * @property string|null $description
 * @property string|null $thumbnail_url
 * @property string|null $creator
 * @property string|null $publisher
 * @property string|null $attribution_text
 * @property MediaStatus $status
 * @property MediaModerationStatus $moderation_status
 * @property MediaVisibility $visibility
 * @property int $lock_version
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $archived_at
 * @property CarbonImmutable|null $takedown_at
 */
class ExternalEmbed extends Model
{
    /** @use HasFactory<ExternalEmbedFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['owner_user_id', 'universe_id', 'source_id', 'content_license_id', 'provider', 'provider_content_id', 'canonical_url', 'embed_url', 'kind', 'title', 'description', 'thumbnail_url', 'creator', 'publisher', 'attribution_text', 'status', 'moderation_status', 'visibility', 'provider_metadata', 'published_at', 'archived_at', 'takedown_at', 'lock_version', 'created_by', 'updated_by'];

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

    /** @return HasMany<MediaAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(MediaAttachment::class);
    }

    /**
     * @param  Builder<ExternalEmbed>  $query
     * @return Builder<ExternalEmbed>
     */
    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->where('status', MediaStatus::Published)->where('moderation_status', MediaModerationStatus::Approved)->where('visibility', MediaVisibility::Public)->whereNull('archived_at')->whereNull('takedown_at');
    }

    protected function casts(): array
    {
        return ['provider' => ExternalMediaProvider::class, 'kind' => MediaKind::class, 'status' => MediaStatus::class, 'moderation_status' => MediaModerationStatus::class, 'visibility' => MediaVisibility::class, 'provider_metadata' => 'array', 'published_at' => 'immutable_datetime', 'archived_at' => 'immutable_datetime', 'takedown_at' => 'immutable_datetime', 'lock_version' => 'integer'];
    }
}
