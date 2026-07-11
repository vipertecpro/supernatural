<?php

namespace App\Models;

use App\Enums\SpoilerSeverity;
use Database\Factories\SpoilerConstraintFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['universe_id', 'severity', 'earliest_progress', 'warning', 'metadata'])]
class SpoilerConstraint extends Model
{
    /** @use HasFactory<SpoilerConstraintFactory> */
    use HasFactory;

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return MorphTo<Model, $this> */
    public function spoilerable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'severity' => SpoilerSeverity::class,
            'earliest_progress' => 'array',
            'metadata' => 'array',
        ];
    }
}
