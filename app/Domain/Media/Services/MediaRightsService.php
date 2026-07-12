<?php

namespace App\Domain\Media\Services;

use App\Enums\MediaOrigin;
use App\Enums\RightsUseType;
use App\Models\ExternalEmbed;
use App\Models\MediaAsset;
use App\Models\SourceRightsReview;

class MediaRightsService
{
    /** Determine whether a hosted asset has effective hosting rights. */
    public function canHost(MediaAsset $asset): bool
    {
        if (in_array($asset->origin, [MediaOrigin::ProjectOriginal, MediaOrigin::UserOwned], true)) {
            return true;
        }

        return $asset->source_id !== null && $this->sourcePermits($asset->source_id, RightsUseType::Hosting);
    }

    /** Determine whether a provider embed has effective embedding rights. */
    public function canEmbed(ExternalEmbed $embed): bool
    {
        return $this->sourcePermits($embed->source_id, RightsUseType::Embedding);
    }

    private function sourcePermits(int $sourceId, RightsUseType $useType): bool
    {
        $review = SourceRightsReview::query()
            ->where('source_id', $sourceId)
            ->where('use_type', $useType)
            ->orderByDesc('assessed_at')
            ->orderByDesc('id')
            ->first();

        return $review?->isEffective() === true;
    }
}
