<?php

namespace App\Domain\UserJourney\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\UserJourney\Exceptions\InvalidJourneyOperation;
use App\Domain\UserJourney\Services\JourneyTargetRegistry;
use App\Enums\PersonalVisibility;
use App\Enums\PublicationStatus;
use App\Models\User;
use App\Models\ViewingOrder;
use App\Models\ViewingOrderItem;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class ManageViewingOrders
{
    public function __construct(
        private readonly JourneyTargetRegistry $targets,
        private readonly AuditLogger $auditLogger,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function addItem(ViewingOrder $order, array $attributes, User $actor): ViewingOrderItem
    {
        $target = $this->targets->resolve((string) $attributes['target_type'], (int) $attributes['target_id'], $this->targets->viewingOrderTypes());
        if ($this->targets->universeId($target) !== $order->universe_id) {
            throw new InvalidJourneyOperation('Viewing-order items must belong to the order universe.', 'cross_universe_viewing_order_item');
        }
        if ($order->status === PublicationStatus::Published) {
            $this->targets->ensurePublic($target);
        }

        return DB::transaction(function () use ($order, $attributes, $actor): ViewingOrderItem {
            ViewingOrder::query()->lockForUpdate()->findOrFail($order->id);

            return ViewingOrderItem::query()->create([
                ...$attributes,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
                'lock_version' => 0,
            ]);
        }, attempts: 3);
    }

    /** @param list<int> $orderedItemIds */
    public function reorder(ViewingOrder $order, array $orderedItemIds, int $expectedVersion, User $actor): ViewingOrder
    {
        return DB::transaction(function () use ($order, $orderedItemIds, $expectedVersion, $actor): ViewingOrder {
            $locked = ViewingOrder::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }

            $existingIds = $locked->items()->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
            $expectedIds = $existingIds;
            $receivedIds = $orderedItemIds;
            sort($expectedIds);
            sort($receivedIds);
            if (count($orderedItemIds) !== count(array_unique($orderedItemIds)) || $expectedIds !== $receivedIds) {
                throw new InvalidJourneyOperation('A reorder must contain every item exactly once.', 'invalid_viewing_order_reorder');
            }

            $offset = count($orderedItemIds) + 1000;
            ViewingOrderItem::query()->where('viewing_order_id', $locked->id)->increment('position', $offset);
            foreach ($orderedItemIds as $position => $itemId) {
                ViewingOrderItem::query()->whereKey($itemId)->update(['position' => $position + 1, 'updated_by' => $actor->id]);
            }
            $locked->update(['lock_version' => $expectedVersion + 1, 'updated_by' => $actor->id]);

            return $locked->fresh('items');
        }, attempts: 3);
    }

    public function setDefault(ViewingOrder $order, int $expectedVersion, User $actor): ViewingOrder
    {
        return DB::transaction(function () use ($order, $expectedVersion, $actor): ViewingOrder {
            $locked = ViewingOrder::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }

            ViewingOrder::query()->where('universe_id', $locked->universe_id)->where('is_default', true)->update(['is_default' => false, 'default_key' => null]);
            $locked->update(['is_default' => true, 'default_key' => 'default', 'lock_version' => $expectedVersion + 1, 'updated_by' => $actor->id]);
            $this->auditLogger->record('journey.viewing_order_default_changed', $locked, ['universe_id' => $locked->universe_id], $actor);

            return $locked->fresh();
        }, attempts: 3);
    }

    public function publish(ViewingOrder $order, int $expectedVersion, User $actor): ViewingOrder
    {
        return $this->transition($order, $expectedVersion, PublicationStatus::Published, $actor);
    }

    public function archive(ViewingOrder $order, int $expectedVersion, User $actor): ViewingOrder
    {
        return $this->transition($order, $expectedVersion, PublicationStatus::Archived, $actor);
    }

    private function transition(ViewingOrder $order, int $expectedVersion, PublicationStatus $status, User $actor): ViewingOrder
    {
        return DB::transaction(function () use ($order, $expectedVersion, $status, $actor): ViewingOrder {
            $locked = ViewingOrder::query()->with('items.target')->lockForUpdate()->findOrFail($order->id);
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            if ($status === PublicationStatus::Published && $locked->items->isEmpty()) {
                throw new InvalidJourneyOperation('A viewing order must contain at least one item before publication.');
            }
            if ($status === PublicationStatus::Published) {
                foreach ($locked->items as $item) {
                    $this->targets->ensurePublic($item->target);
                }
            }

            $locked->update([
                'status' => $status,
                'visibility' => $status === PublicationStatus::Published ? PersonalVisibility::Public : $locked->visibility,
                'published_at' => $status === PublicationStatus::Published ? now() : $locked->published_at,
                'archived_at' => $status === PublicationStatus::Archived ? now() : null,
                'lock_version' => $expectedVersion + 1,
                'updated_by' => $actor->id,
            ]);
            $this->auditLogger->record('journey.viewing_order_'.$status->value, $locked, ['version' => $locked->lock_version], $actor);

            return $locked->fresh('items');
        }, attempts: 3);
    }
}
