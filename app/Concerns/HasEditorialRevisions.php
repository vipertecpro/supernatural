<?php

namespace App\Concerns;

use App\Models\Citation;
use App\Models\EditorialRevision;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasEditorialRevisions
{
    /** @return MorphMany<EditorialRevision, $this> */
    public function editorialRevisions(): MorphMany
    {
        return $this->morphMany(EditorialRevision::class, 'revisable');
    }

    /** @return MorphMany<Citation, $this> */
    public function citations(): MorphMany
    {
        return $this->morphMany(Citation::class, 'citable');
    }
}
