<?php

namespace App\Concerns;

use App\Enums\ContentRestrictionType;
use App\Enums\RestrictionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait HasModerationRestrictions
{
    /** @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeWithoutActivePublicRestriction(Builder $query): Builder
    {
        $model = $query->getModel();

        return $query->whereNotExists(function (QueryBuilder $restricted) use ($model): void {
            $restricted->selectRaw('1')->from('content_restrictions')
                ->where('target_type', $model->getMorphClass())
                ->whereColumn('target_id', $model->qualifyColumn($model->getKeyName()))
                ->where('status', RestrictionStatus::Active->value)
                ->whereIn('type', [ContentRestrictionType::HiddenFromPublic->value, ContentRestrictionType::TakedownRestricted->value])
                ->where('effective_at', '<=', now())
                ->where(fn (QueryBuilder $expiry) => $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now()));
        });
    }
}
