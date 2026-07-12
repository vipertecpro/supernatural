<?php

namespace App\Models;

use Database\Factories\ViewingOrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $viewing_order_id
 * @property string $target_type
 * @property int $target_id
 * @property int $position
 * @property string|null $group_label
 * @property string|null $display_title
 * @property string|null $explanation
 * @property bool $is_optional
 * @property bool $is_skippable
 * @property int $lock_version
 */
#[Fillable(['viewing_order_id', 'target_type', 'target_id', 'position', 'group_label', 'display_title', 'explanation', 'is_optional', 'is_skippable', 'spoiler_constraint_id', 'created_by', 'updated_by', 'lock_version'])]
class ViewingOrderItem extends Model
{
    /** @use HasFactory<ViewingOrderItemFactory> */
    use HasFactory;

    /** @return BelongsTo<ViewingOrder, $this> */
    public function viewingOrder(): BelongsTo
    {
        return $this->belongsTo(ViewingOrder::class);
    }

    /** @return MorphTo<Model, $this> */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_optional' => 'boolean', 'is_skippable' => 'boolean', 'position' => 'integer', 'lock_version' => 'integer'];
    }
}
