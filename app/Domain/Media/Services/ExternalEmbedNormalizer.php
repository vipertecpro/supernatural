<?php

namespace App\Domain\Media\Services;

use App\Domain\Media\Exceptions\InvalidMediaOperation;
use App\Enums\ExternalMediaProvider;

class ExternalEmbedNormalizer
{
    /**
     * Normalize an allowlisted provider URL without making a network request.
     *
     * @return array{provider_content_id:string,canonical_url:string,embed_url:string}
     */
    public function normalize(ExternalMediaProvider $provider, string $url): array
    {
        if (str_contains(strtolower($url), '<iframe') || str_contains(strtolower($url), '<script')) {
            throw new InvalidMediaOperation('Embed HTML is not accepted.', 'unsupported_embed_input');
        }

        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $allowedHosts = config("media.providers.{$provider->value}.hosts", []);
        if ($scheme !== 'https' || ! in_array($host, $allowedHosts, true)) {
            throw new InvalidMediaOperation('The external media provider URL is not allowlisted.', 'unsupported_media_provider');
        }

        $path = trim((string) ($parts['path'] ?? ''), '/');
        parse_str((string) ($parts['query'] ?? ''), $query);
        $id = match ($provider) {
            ExternalMediaProvider::YouTube => $host === 'youtu.be' ? strtok($path, '/') : ($query['v'] ?? null),
            ExternalMediaProvider::Vimeo => preg_match('/^[0-9]+$/', $path) === 1 ? $path : null,
            ExternalMediaProvider::Spotify => preg_match('#^(track|episode|show|playlist)/([A-Za-z0-9]+)$#', $path, $matches) === 1 ? "{$matches[1]}/{$matches[2]}" : null,
            ExternalMediaProvider::SoundCloud => $path !== '' ? substr(hash('sha256', "https://{$host}/{$path}"), 0, 32) : null,
        };
        if (! is_string($id) || $id === '' || strlen($id) > 255) {
            throw new InvalidMediaOperation('The provider content identifier is invalid.', 'invalid_provider_content_id');
        }

        $canonical = match ($provider) {
            ExternalMediaProvider::YouTube => "https://www.youtube.com/watch?v={$id}",
            ExternalMediaProvider::Vimeo => "https://vimeo.com/{$id}",
            ExternalMediaProvider::Spotify => "https://open.spotify.com/{$id}",
            ExternalMediaProvider::SoundCloud => "https://{$host}/{$path}",
        };
        $embedHost = (string) config("media.providers.{$provider->value}.embed_host");
        $embedUrl = match ($provider) {
            ExternalMediaProvider::YouTube => "https://{$embedHost}/embed/{$id}",
            ExternalMediaProvider::Vimeo => "https://{$embedHost}/video/{$id}",
            ExternalMediaProvider::Spotify => "https://{$embedHost}/embed/{$id}",
            ExternalMediaProvider::SoundCloud => "https://{$embedHost}/player/?url=".rawurlencode($canonical),
        };

        return ['provider_content_id' => $id, 'canonical_url' => $canonical, 'embed_url' => $embedUrl];
    }
}
