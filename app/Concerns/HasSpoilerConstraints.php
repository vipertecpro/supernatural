<?php

namespace App\Concerns;

use App\Models\SpoilerConstraint;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSpoilerConstraints
{
    /**
     * Get spoiler constraints attached to this model.
     *
     * @return MorphMany<SpoilerConstraint, $this>
     */
    public function spoilerConstraints(): MorphMany
    {
        return $this->morphMany(SpoilerConstraint::class, 'spoilerable');
    }
}
