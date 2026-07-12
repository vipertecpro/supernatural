<?php

namespace App\Models;

use Database\Factories\SpoilerBoundaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $spoiler_constraint_id
 * @property int $work_id
 * @property int|null $season_id
 * @property int|null $episode_id
 */
#[Fillable(['spoiler_constraint_id', 'work_id', 'season_id', 'episode_id'])]
class SpoilerBoundary extends Model
{
    /** @use HasFactory<SpoilerBoundaryFactory> */
    use HasFactory;

    /** @return BelongsTo<SpoilerConstraint, $this> */
    public function constraint(): BelongsTo
    {
        return $this->belongsTo(SpoilerConstraint::class, 'spoiler_constraint_id');
    }

    /** @return BelongsTo<Work, $this> */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /** @return BelongsTo<Season, $this> */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /** @return BelongsTo<Episode, $this> */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }
}
