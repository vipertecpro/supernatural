<?php

namespace App\Http\Controllers;

use App\Domain\Media\Services\ExperienceMediaService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function __construct(
        private readonly ExperienceMediaService $experienceMedia,
    ) {}

    public function home(): Response
    {
        return $this->render('welcome', 'A living archive for every story', 'Explore connected fictional worlds, follow your private viewing journey, and contribute evidence-led knowledge in a spoiler-aware community.', 'home', [
            'experienceMedia' => $this->experienceMedia->publicExperienceMedia(),
        ]);
    }

    public function about(): Response
    {
        return $this->render('public/about', 'About', 'Why The Archive connects structured knowledge, private progress, spoiler safety, evidence, and community in one fandom-neutral platform.', 'about');
    }

    public function openSource(): Response
    {
        return $this->render('public/open-source', 'Open Source', 'Explore the architecture, engineering principles, governance boundaries, and current source-availability status behind The Archive.', 'open-source');
    }

    public function accessibility(): Response
    {
        return $this->render('public/accessibility', 'Accessibility', 'Read The Archive accessibility target, implemented principles, known limitations, and issue-reporting guidance.', 'accessibility');
    }

    public function contentPolicy(): Response
    {
        return $this->render('public/content-policy', 'Content Policy', 'Review the rights, attribution, user-content, spoiler, safety, moderation, and appeal principles for The Archive.', 'content-policy');
    }

    public function copyrightAndTakedown(): Response
    {
        return $this->render('public/copyright-and-takedown', 'Copyright and Takedown', 'Review the unofficial project disclaimer, media boundaries, and private follow-up process for rights concerns.', 'copyright-and-takedown');
    }

    /**
     * Render a static public page with bounded, non-sensitive metadata props.
     *
     * @param  array<string, mixed>  $additionalProps
     */
    private function render(string $component, string $title, string $description, string $routeName, array $additionalProps = []): Response
    {
        $canonicalUrl = $this->canonicalUrl($routeName);
        $siteName = (string) config('public-site.name', 'The Archive');

        return Inertia::render($component, [
            'publicSite' => [
                'name' => $siteName,
                'registrationAvailable' => Route::has('register'),
                'repositoryUrl' => $this->repositoryUrl(),
                'currentYear' => now()->year,
                'metadata' => [
                    'title' => $title,
                    'description' => $description,
                    'canonicalUrl' => $canonicalUrl,
                    'openGraphType' => 'website',
                    'robots' => 'index, follow',
                    'themeColor' => '#11171c',
                ],
                'structuredData' => $this->structuredData($routeName, $siteName, $description, $canonicalUrl),
            ],
            ...$additionalProps,
        ]);
    }

    /**
     * Build conservative schema data without inventing URLs or organization facts.
     *
     * @return array<int, array<string, mixed>>
     */
    private function structuredData(string $routeName, string $siteName, string $description, ?string $canonicalUrl): array
    {
        if ($canonicalUrl === null || ! in_array($routeName, ['home', 'open-source'], true)) {
            return [];
        }

        if ($routeName === 'home') {
            return [[
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $siteName,
                'description' => $description,
                'url' => $canonicalUrl,
            ]];
        }

        return [[
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $siteName,
            'description' => $description,
            'applicationCategory' => 'EntertainmentApplication',
            'operatingSystem' => 'Web',
            'url' => $canonicalUrl,
        ]];
    }

    private function canonicalUrl(string $routeName): ?string
    {
        $baseUrl = $this->safeHttpsUrl(config('public-site.url'));

        if (
            $baseUrl === null
            || ! in_array(parse_url($baseUrl, PHP_URL_PATH), [null, '', '/'], true)
            || parse_url($baseUrl, PHP_URL_QUERY) !== null
            || parse_url($baseUrl, PHP_URL_FRAGMENT) !== null
        ) {
            return null;
        }

        $path = parse_url(route($routeName, absolute: false), PHP_URL_PATH);

        return rtrim($baseUrl, '/').($path === '/' ? '/' : '/'.ltrim((string) $path, '/'));
    }

    private function repositoryUrl(): ?string
    {
        $url = $this->safeHttpsUrl(config('public-site.repository_url'));

        if ($url === null) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, ['github.com', 'gitlab.com', 'codeberg.org'], true) ? $url : null;
    }

    private function safeHttpsUrl(mixed $value): ?string
    {
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        if (strtolower((string) parse_url($value, PHP_URL_SCHEME)) !== 'https') {
            return null;
        }

        if (parse_url($value, PHP_URL_USER) !== null || parse_url($value, PHP_URL_PASS) !== null) {
            return null;
        }

        return rtrim($value, '/');
    }
}
