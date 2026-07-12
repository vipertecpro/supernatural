<?php

namespace App\Domain\Media\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Media\Exceptions\InvalidMediaOperation;
use App\Domain\Moderation\Services\RestrictionEvaluator;
use App\Enums\PublicationStatus;
use App\Models\Episode;
use App\Models\ExternalEmbed;
use App\Models\Franchise;
use App\Models\LoreEntity;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\Season;
use App\Models\Timeline;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class AttachMedia
{
    /** @var list<string> */
    private const TARGET_TYPES = ['universe', 'franchise', 'work', 'work_translation', 'season', 'episode', 'lore_entity', 'lore_entity_translation', 'lore_alias', 'entity_appearance', 'lore_relationship', 'timeline', 'timeline_entry'];

    public function __construct(private readonly AuditLogger $auditLogger, private readonly RestrictionEvaluator $restrictions) {}

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes, User $actor): MediaAttachment
    {
        $target = $this->resolveTarget((string) $attributes['attachable_type'], (int) $attributes['attachable_id']);
        if ($this->restrictions->areAttachmentsBlocked($target)) {
            throw new InvalidMediaOperation('New media attachments are restricted for this target.', 'content_attachments_restricted');
        }
        $media = $this->resolveMedia($attributes);
        $this->assertUniverseCompatible($media, $target);
        $attributes['locale'] = isset($attributes['locale']) ? str((string) $attributes['locale'])->replace('_', '-')->lower()->toString() : null;
        $attributes['is_primary'] = (bool) ($attributes['is_primary'] ?? false);
        $attributes['primary_key'] = $attributes['is_primary'] ? 'primary' : null;
        $attributes['status'] = PublicationStatus::Draft;
        $attributes['created_by'] = $actor->id;
        $attributes['updated_by'] = $actor->id;

        return DB::transaction(function () use ($attributes, $actor): MediaAttachment {
            $duplicate = MediaAttachment::query()->where('attachable_type', $attributes['attachable_type'])->where('attachable_id', $attributes['attachable_id'])->where('role', $attributes['role'])->where('media_asset_id', $attributes['media_asset_id'] ?? null)->where('external_embed_id', $attributes['external_embed_id'] ?? null)->whereNull('deleted_at')->exists();
            if ($duplicate) {
                throw new InvalidMediaOperation('This active media attachment already exists.', 'duplicate_media_attachment');
            }
            $attachment = MediaAttachment::query()->create($attributes);
            $this->auditLogger->record('media.attached', $attachment, ['target_type' => $attachment->attachable_type, 'role' => $attachment->role->value], $actor);

            return $attachment;
        });
    }

    /** Publish an attachment only when both target and media are public. */
    public function publish(MediaAttachment $attachment, User $actor, int $expectedVersion): MediaAttachment
    {
        return DB::transaction(function () use ($attachment, $actor, $expectedVersion): MediaAttachment {
            $locked = MediaAttachment::query()->lockForUpdate()->findOrFail($attachment->id);
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            $mediaIsPublic = $locked->media_asset_id !== null
                ? MediaAsset::query()->visibleToPublic()->whereKey($locked->media_asset_id)->exists()
                : ExternalEmbed::query()->visibleToPublic()->whereKey($locked->external_embed_id)->exists();
            $target = $this->resolveTarget($locked->attachable_type, $locked->attachable_id);
            if (! $mediaIsPublic || ! $this->targetIsPublic($target) || $this->restrictions->areAttachmentsBlocked($target)) {
                throw new InvalidMediaOperation('Both media and attachment target must be public before attachment publication.', 'media_attachment_not_publishable');
            }
            $locked->update(['status' => PublicationStatus::Published, 'updated_by' => $actor->id, 'lock_version' => $expectedVersion + 1]);
            $this->auditLogger->record('media.attachment_published', $locked, ['version' => $expectedVersion + 1], $actor);

            return $locked->fresh();
        });
    }

    /** Detach media while retaining a soft-deleted history row. */
    public function delete(MediaAttachment $attachment, User $actor): void
    {
        $this->auditLogger->record('media.detached', $attachment, ['target_type' => $attachment->attachable_type], $actor);
        $attachment->delete();
    }

    /** @param array<string, mixed> $attributes */
    private function resolveMedia(array $attributes): Model
    {
        $assetId = $attributes['media_asset_id'] ?? null;
        $embedId = $attributes['external_embed_id'] ?? null;
        if (($assetId === null) === ($embedId === null)) {
            throw new InvalidMediaOperation('Exactly one hosted asset or external embed is required.', 'invalid_media_source');
        }

        return $assetId !== null ? MediaAsset::query()->findOrFail((int) $assetId) : ExternalEmbed::query()->findOrFail((int) $embedId);
    }

    private function resolveTarget(string $type, int $id): Model
    {
        if (! in_array($type, self::TARGET_TYPES, true)) {
            throw new InvalidMediaOperation('The media attachment target type is not allowlisted.', 'invalid_media_target');
        }
        $class = Relation::getMorphedModel($type);
        if ($class === null) {
            throw new InvalidMediaOperation('The media attachment target type is not registered.', 'invalid_media_target');
        }

        return $class::query()->findOrFail($id);
    }

    private function assertUniverseCompatible(Model $media, Model $target): void
    {
        $mediaUniverse = $media->getAttribute('universe_id');
        $targetUniverse = match (true) {
            $target instanceof Universe => $target->id,
            $target->getAttribute('universe_id') !== null => $target->getAttribute('universe_id'),
            method_exists($target, 'work') => $target->work()->value('universe_id'),
            method_exists($target, 'loreEntity') => $target->loreEntity()->value('universe_id'),
            method_exists($target, 'timeline') => $target->timeline()->value('universe_id'),
            default => null,
        };
        if ($mediaUniverse !== null && $targetUniverse !== null && (int) $mediaUniverse !== (int) $targetUniverse) {
            throw new InvalidMediaOperation('Media and target must belong to the same universe.', 'cross_universe_media_attachment');
        }
    }

    private function targetIsPublic(Model $target): bool
    {
        return match (true) {
            $target instanceof Universe => $target->status === PublicationStatus::Published && (bool) $target->is_public,
            $target instanceof Franchise => Franchise::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof Work => Work::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof Season => Season::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof Episode => Episode::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof LoreEntity => LoreEntity::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof Timeline => Timeline::query()->visibleToPublic()->whereKey($target)->exists(),
            default => $target->getAttribute('status') === PublicationStatus::Published,
        };
    }
}
