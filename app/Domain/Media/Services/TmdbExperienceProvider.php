<?php

namespace App\Domain\Media\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TmdbExperienceProvider
{
    /**
     * Return rights-gated remote image metadata without proxying image bytes.
     *
     * @return array{enabled:bool,attribution:string|null,notice:string|null,images:array<int, array{key:string,alt:string,src:string,srcSet:string,width:int,height:int}>}
     */
    public function images(): array
    {
        if (! $this->isEnabled()) {
            return $this->emptyResult();
        }

        $seriesId = (string) config('public-site.tmdb.series_id');

        try {
            /** @var array<string, mixed> $payload */
            $payload = Cache::remember(
                "experience.tmdb.series.{$seriesId}.images",
                now()->addHours(6),
                fn (): array => Http::withToken((string) config('public-site.tmdb.token'))
                    ->acceptJson()
                    ->timeout(5)
                    ->get("https://api.themoviedb.org/3/tv/{$seriesId}/images", [
                        'include_image_language' => 'en,null',
                    ])
                    ->throw()
                    ->json(),
            );
        } catch (\Throwable) {
            return $this->emptyResult();
        }

        $images = collect($this->backdropRecords($payload['backdrops'] ?? null))
            ->filter(fn (array $image): bool => $this->isSafePath($image['file_path'] ?? null))
            ->take(8)
            ->values()
            ->map(function (array $image, int $index): array {
                $path = (string) $image['file_path'];

                return [
                    'key' => 'tmdb-backdrop-'.($index + 1),
                    'alt' => 'Approved series backdrop '.($index + 1),
                    'src' => $this->imageUrl('w780', $path),
                    'srcSet' => implode(', ', [
                        $this->imageUrl('w300', $path).' 300w',
                        $this->imageUrl('w780', $path).' 780w',
                        $this->imageUrl('original', $path).' 1280w',
                    ]),
                    'width' => max(1, (int) ($image['width'] ?? 1280)),
                    'height' => max(1, (int) ($image['height'] ?? 720)),
                ];
            })
            ->all();

        return [
            'enabled' => $images !== [],
            'attribution' => 'Image metadata and delivery provided by TMDB.',
            'notice' => 'This product uses the TMDB API but is not endorsed or certified by TMDB.',
            'images' => $images,
        ];
    }

    private function isEnabled(): bool
    {
        $baseUrl = rtrim((string) config('public-site.tmdb.image_base_url'), '/');

        return filled(config('public-site.tmdb.token'))
            && ctype_digit((string) config('public-site.tmdb.series_id'))
            && config('public-site.tmdb.terms_accepted') === true
            && (config('public-site.commercial') !== true || config('public-site.tmdb.commercial_licensed') === true)
            && $baseUrl === 'https://image.tmdb.org/t/p';
    }

    private function isSafePath(mixed $path): bool
    {
        return is_string($path) && preg_match('#^/[A-Za-z0-9._-]+$#', $path) === 1;
    }

    /** @return array<int, array<string, mixed>> */
    private function backdropRecords(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $records = [];

        foreach ($value as $record) {
            if (is_array($record)) {
                $records[] = $record;
            }
        }

        return $records;
    }

    private function imageUrl(string $size, string $path): string
    {
        return 'https://image.tmdb.org/t/p/'.$size.$path;
    }

    /** @return array{enabled:false,attribution:null,notice:null,images:array{}} */
    private function emptyResult(): array
    {
        return ['enabled' => false, 'attribution' => null, 'notice' => null, 'images' => []];
    }
}
