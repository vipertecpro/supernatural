<?php

namespace App\Domain\Media\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Enums\PermissionName;
use App\Models\ExternalEmbed;
use App\Models\MediaAsset;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateMedia
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $attributes */
    public function handle(MediaAsset|ExternalEmbed $media, array $attributes, User $actor): MediaAsset|ExternalEmbed
    {
        return DB::transaction(function () use ($media, $attributes, $actor): MediaAsset|ExternalEmbed {
            $locked = $media->newQuery()->lockForUpdate()->findOrFail((int) $media->getKey());
            $expectedVersion = (int) $attributes['expected_version'];
            if ((int) $locked->getAttribute('lock_version') !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            unset($attributes['expected_version']);
            if (array_key_exists('moderation_status', $attributes) && ! $actor->hasPermission(PermissionName::MediaModerate)) {
                unset($attributes['moderation_status']);
            }
            $attributes['updated_by'] = $actor->id;
            $attributes['lock_version'] = $expectedVersion + 1;
            $locked->update($attributes);
            $this->auditLogger->record('media.updated', $locked, ['version' => $expectedVersion + 1, 'moderation_changed' => array_key_exists('moderation_status', $attributes)], $actor);

            return $locked->fresh();
        });
    }
}
