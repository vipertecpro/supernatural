<?php

namespace App\Listeners;

use App\Domain\Search\Services\SearchProjector;
use App\Events\EditorialRevisionApplied;
use App\Events\LoreEntityPublished;
use App\Events\SearchProjectionRemovalRequested;
use App\Events\SearchProjectionRequested;
use App\Events\TimelinePublished;
use App\Models\LoreEntity;
use App\Models\SearchDocument;
use App\Models\Timeline;
use Illuminate\Database\Eloquent\Relations\Relation;

class RefreshSearchProjection
{
    public function __construct(private readonly SearchProjector $projector) {}

    /** Consume projection events idempotently after authoritative commits. */
    public function handle(object $event): void
    {
        [$type, $id, $remove] = match (true) {
            $event instanceof SearchProjectionRequested => [$event->sourceType, $event->sourceId, false],
            $event instanceof SearchProjectionRemovalRequested => [$event->sourceType, $event->sourceId, true],
            $event instanceof LoreEntityPublished => [(new LoreEntity)->getMorphClass(), $event->loreEntityId, false],
            $event instanceof TimelinePublished => [(new Timeline)->getMorphClass(), $event->timelineId, false],
            $event instanceof EditorialRevisionApplied => [$event->targetType, $event->targetId, false],
            default => [null, null, false],
        };
        if (! is_string($type) || ! is_int($id)) {
            return;
        }
        $class = Relation::getMorphedModel($type);
        $model = $class === null ? null : $class::query()->find($id);
        if ($model === null) {
            SearchDocument::query()->where('source_type', $type)->where('source_id', $id)->delete();

            return;
        }
        $remove ? $this->projector->remove($model) : $this->projector->project($model);
    }
}
