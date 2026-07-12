<?php

namespace App\Domain\Media\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Media\Exceptions\InvalidMediaOperation;
use App\Domain\Media\Services\MediaRightsService;
use App\Enums\MediaModerationStatus;
use App\Enums\MediaProcessingStatus;
use App\Enums\MediaStatus;
use App\Enums\MediaVisibility;
use App\Events\MediaArchived;
use App\Events\MediaPublished;
use App\Models\ExternalEmbed;
use App\Models\MediaAsset;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class TransitionMedia
{
    public function __construct(private readonly MediaRightsService $rights, private readonly AuditLogger $auditLogger) {}

    /** Publish an approved, rights-safe media record. */
    public function publish(MediaAsset|ExternalEmbed $media, User $actor, int $expectedVersion): MediaAsset|ExternalEmbed
    {
        return DB::transaction(function () use ($media, $actor, $expectedVersion): MediaAsset|ExternalEmbed {
            $locked = $media->newQuery()->lockForUpdate()->findOrFail((int) $media->getKey());
            $this->assertVersion($locked, $expectedVersion);
            if ($locked->moderation_status !== MediaModerationStatus::Approved) {
                throw new InvalidMediaOperation('Media must be approved by moderation before publication.', 'media_moderation_required');
            }
            $hasRights = $locked instanceof MediaAsset ? $this->rights->canHost($locked) : $this->rights->canEmbed($locked);
            if (! $hasRights) {
                throw new InvalidMediaOperation('Effective rights approval is required before publication.', 'media_rights_required');
            }
            if ($locked instanceof MediaAsset && $locked->processing_status !== MediaProcessingStatus::Ready) {
                throw new InvalidMediaOperation('Media processing must be ready before publication.', 'media_processing_required');
            }
            if ($locked->takedown_at !== null) {
                throw new InvalidMediaOperation('Takedown media cannot be republished.', 'media_takedown_blocked');
            }
            $locked->update(['status' => MediaStatus::Published, 'visibility' => MediaVisibility::Public, 'published_at' => now(), 'archived_at' => null, 'updated_by' => $actor->id, 'lock_version' => $expectedVersion + 1]);
            $type = $locked instanceof MediaAsset ? 'media_asset' : 'external_embed';
            $this->auditLogger->record('media.published', $locked, ['type' => $type, 'version' => $expectedVersion + 1], $actor);
            MediaPublished::dispatch($type, (int) $locked->getKey(), $actor->id);

            return $locked->fresh();
        });
    }

    /** Archive a media record without deleting its rights or attachment history. */
    public function archive(MediaAsset|ExternalEmbed $media, User $actor, int $expectedVersion): MediaAsset|ExternalEmbed
    {
        return DB::transaction(function () use ($media, $actor, $expectedVersion): MediaAsset|ExternalEmbed {
            $locked = $media->newQuery()->lockForUpdate()->findOrFail((int) $media->getKey());
            $this->assertVersion($locked, $expectedVersion);
            $locked->update(['status' => MediaStatus::Archived, 'visibility' => MediaVisibility::Restricted, 'archived_at' => now(), 'updated_by' => $actor->id, 'lock_version' => $expectedVersion + 1]);
            $type = $locked instanceof MediaAsset ? 'media_asset' : 'external_embed';
            $this->auditLogger->record('media.archived', $locked, ['type' => $type, 'version' => $expectedVersion + 1], $actor);
            MediaArchived::dispatch($type, (int) $locked->getKey(), $actor->id);

            return $locked->fresh();
        });
    }

    private function assertVersion(MediaAsset|ExternalEmbed $media, int $expectedVersion): void
    {
        if ((int) $media->getAttribute('lock_version') !== $expectedVersion) {
            throw new OptimisticLockConflict;
        }
    }
}
