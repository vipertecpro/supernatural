<?php

namespace App\Models;

use Database\Factories\SpoilerCorrectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['spoiler_constraint_id', 'corrected_by_user_id', 'reason', 'previous_classification', 'corrected_at'])]
class SpoilerCorrection extends Model
{
    /** @use HasFactory<SpoilerCorrectionFactory> */
    use HasFactory;

    /** @return BelongsTo<SpoilerConstraint, $this> */
    public function constraint(): BelongsTo
    {
        return $this->belongsTo(SpoilerConstraint::class, 'spoiler_constraint_id');
    }

    /** @return BelongsTo<User, $this> */
    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by_user_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'previous_classification' => 'array',
            'corrected_at' => 'immutable_datetime',
        ];
    }
}
