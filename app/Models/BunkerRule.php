<?php

namespace App\Models;

use App\Enums\BunkerRuleCategory;
use Database\Factories\BunkerRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $bunker_id
 * @property string $title
 * @property int $position
 * @property BunkerRuleCategory $category
 * @property string $description
 * @property bool $is_active
 * @property int $lock_version
 * @property Bunker $bunker
 */
class BunkerRule extends Model
{
    /** @use HasFactory<BunkerRuleFactory> */
    use HasFactory;

    protected $fillable = ['bunker_id', 'title', 'description', 'category', 'position', 'is_active', 'created_by', 'updated_by', 'lock_version'];

    /** @return BelongsTo<Bunker, $this> */
    public function bunker(): BelongsTo
    {
        return $this->belongsTo(Bunker::class);
    }

    protected function casts(): array
    {
        return ['category' => BunkerRuleCategory::class, 'position' => 'integer', 'is_active' => 'boolean', 'lock_version' => 'integer'];
    }
}
