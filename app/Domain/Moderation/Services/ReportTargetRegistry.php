<?php

namespace App\Domain\Moderation\Services;

use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Enums\PublicationStatus;
use App\Models\EntityAppearance;
use App\Models\Episode;
use App\Models\ExternalEmbed;
use App\Models\Franchise;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Models\LoreRelationship;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\Season;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\Universe;
use App\Models\User;
use App\Models\ViewingOrder;
use App\Models\Work;
use App\Models\WorkTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class ReportTargetRegistry
{
    /** @var list<string> */
    public const ALIASES = ['user', 'universe', 'franchise', 'work', 'work_translation', 'season', 'episode', 'lore_entity', 'lore_alias', 'entity_appearance', 'lore_relationship', 'timeline', 'timeline_entry', 'media_asset', 'external_embed', 'media_attachment', 'viewing_order'];

    public function resolve(string $alias, int $id): Model
    {
        if (! in_array($alias, self::ALIASES, true)) {
            throw new InvalidModerationOperation('The selected report target is not supported.', 'unsupported_report_target');
        }

        $class = Relation::getMorphedModel($alias);
        $target = $class === null ? null : $class::query()->find($id);

        if (! $target instanceof Model) {
            throw new InvalidModerationOperation('The selected report target was not found.', 'report_target_not_found');
        }

        return $target;
    }

    public function isAccessibleToReporter(Model $target, User $reporter): bool
    {
        return match (true) {
            $target instanceof User => $target->id !== $reporter->id,
            $target instanceof Universe => $target->status === PublicationStatus::Published && $target->is_public,
            $target instanceof Franchise, $target instanceof Work, $target instanceof Season, $target instanceof Episode, $target instanceof LoreEntity, $target instanceof Timeline, $target instanceof MediaAsset, $target instanceof ExternalEmbed, $target instanceof ViewingOrder => $target::query()->visibleToPublic()->whereKey($target)->exists(),
            $target instanceof WorkTranslation => $target->status === PublicationStatus::Published && Work::query()->visibleToPublic()->whereKey($target->work_id)->exists(),
            $target instanceof LoreAlias => $target->status === PublicationStatus::Published && LoreEntity::query()->visibleToPublic()->whereKey($target->lore_entity_id)->exists(),
            $target instanceof EntityAppearance => $target->status === PublicationStatus::Published && LoreEntity::query()->visibleToPublic()->whereKey($target->lore_entity_id)->exists(),
            $target instanceof LoreRelationship => $target->status->value === 'published' && LoreEntity::query()->visibleToPublic()->whereKey($target->source_entity_id)->exists(),
            $target instanceof TimelineEntry => $target->status === PublicationStatus::Published && Timeline::query()->visibleToPublic()->whereKey($target->timeline_id)->exists(),
            $target instanceof MediaAttachment => $target->status === PublicationStatus::Published,
            default => false,
        };
    }
}
