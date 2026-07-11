<?php

namespace App\Domain\Catalog\Services;

use App\Enums\SpoilerSeverity;
use Illuminate\Database\Eloquent\Model;

class SpoilerVisibilityService
{
    public function shouldRedact(Model $model): bool
    {
        if (! $model->relationLoaded('spoilerConstraints')) {
            return true;
        }

        $constraints = $model->getRelation('spoilerConstraints');

        return $constraints->isEmpty() || $constraints->contains(
            fn ($constraint): bool => $constraint->severity !== SpoilerSeverity::None,
        );
    }
}
