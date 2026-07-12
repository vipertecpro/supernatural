<?php

namespace App\Domain\Media\Services;

use App\Enums\ExternalMediaProvider;
use App\Models\ExternalEmbed;

class ExperienceMediaService
{
    public function __construct(
        private readonly TmdbExperienceProvider $tmdb,
        private readonly MediaRightsService $rights,
    ) {}

    /**
     * Resolve only public, reviewed, authorized YouTube transmissions.
     *
     * @return array{tmdb:array<string, mixed>,transmission:array<string, mixed>|null}
     */
    public function publicExperienceMedia(): array
    {
        $transmission = ExternalEmbed::query()
            ->visibleToPublic()
            ->where('provider', ExternalMediaProvider::YouTube)
            ->latest('published_at')
            ->get()
            ->first(fn (ExternalEmbed $embed): bool => ($embed->provider_metadata['authorized_channel'] ?? false) === true && $this->rights->canEmbed($embed));

        return [
            'tmdb' => $this->tmdb->images(),
            'transmission' => $transmission === null ? null : [
                'title' => $transmission->title,
                'description' => $transmission->description,
                'provider' => $transmission->provider->value,
                'embedUrl' => $transmission->embed_url,
                'canonicalUrl' => $transmission->canonical_url,
                'attribution' => $transmission->attribution_text,
            ],
        ];
    }
}
