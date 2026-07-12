<?php

use App\Http\Controllers\PublicPageController;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('public pages render their supported inertia components for guests', function (string $routeName, string $component) {
    $this->get(route($routeName))
        ->assertSuccessful()
        ->assertHeader('Content-Security-Policy')
        ->assertHeader('Permissions-Policy')
        ->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->where('publicSite.currentYear', now()->year)
            ->where('publicSite.registrationAvailable', true)
            ->missing('journey')
            ->missing('notifications')
            ->missing('moderation')
            ->missing('roles')
            ->missing('permissions')
            ->missing('drafts'));
})->with([
    'home' => ['home', 'welcome'],
    'about' => ['about', 'public/about'],
    'open source' => ['open-source', 'public/open-source'],
    'accessibility' => ['accessibility', 'public/accessibility'],
    'content policy' => ['content-policy', 'public/content-policy'],
    'copyright and takedown' => ['copyright-and-takedown', 'public/copyright-and-takedown'],
]);

test('authenticated visitors may remain on the public homepage with a safe identity prop', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('auth.user.id', $user->id)
            ->missing('journey')
            ->missing('notifications')
            ->missing('moderation'));
});

test('missing public URL and repository configuration omit invented URLs', function () {
    config()->set('public-site.url');
    config()->set('public-site.repository_url');

    $this->get(route('open-source'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('publicSite.repositoryUrl', null)
            ->where('publicSite.metadata.canonicalUrl', null)
            ->where('publicSite.structuredData', []));
});

test('configured public metadata uses safe canonical and repository URLs', function () {
    config()->set('public-site.url', 'https://archive.example');
    config()->set('public-site.repository_url', 'https://github.com/example/archive');

    $this->get(route('open-source'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('publicSite.repositoryUrl', 'https://github.com/example/archive')
            ->where('publicSite.metadata.canonicalUrl', 'https://archive.example/open-source')
            ->where('publicSite.structuredData.0.@type', 'SoftwareApplication'));
});

test('public canonical configuration must be a clean HTTPS origin', function (string $url) {
    config()->set('public-site.url', $url);

    $this->get(route('about'))
        ->assertInertia(fn (Assert $page) => $page->where('publicSite.metadata.canonicalUrl', null));
})->with([
    'non HTTPS' => 'http://archive.example',
    'path' => 'https://archive.example/public',
    'query' => 'https://archive.example?source=test',
    'fragment' => 'https://archive.example#public',
    'credentials' => 'https://user:secret@archive.example',
]);

test('unsafe repository URLs are rejected', function (string $url) {
    config()->set('public-site.repository_url', $url);

    $this->get(route('open-source'))
        ->assertInertia(fn (Assert $page) => $page->where('publicSite.repositoryUrl', null));
})->with([
    'non HTTPS' => 'http://github.com/example/archive',
    'credentials' => 'https://user:secret@github.com/example/archive',
    'untrusted host' => 'https://example.com/archive',
    'invalid URL' => 'not-a-url',
]);

test('public route source includes no deferred domain pages', function () {
    $routes = file_get_contents(base_path('routes/web.php'));

    expect($routes)
        ->toContain(PublicPageController::class)
        ->not->toContain("Route::get('explore'")
        ->not->toContain("Route::get('lore'")
        ->not->toContain("Route::get('search'")
        ->not->toContain("Route::get('community'")
        ->not->toContain("Route::get('bunkers'")
        ->not->toContain("Route::get('watch-rooms'");
});

test('public frontend contracts retain safe navigation metadata and effects fallbacks', function () {
    $navigation = file_get_contents(resource_path('js/lib/shell/navigation.ts'));
    $effects = file_get_contents(resource_path('js/features/experience/capability-resolver.ts'));
    $metadata = file_get_contents(resource_path('js/components/public/public-head.tsx'));
    $copy = file_get_contents(resource_path('js/content/public-site.ts'));

    expect($navigation)
        ->toContain('home()', 'about()', 'openSource()')
        ->not->toContain('explore()', 'lore()', 'community()', 'bunkers()')
        ->and($effects)
        ->toContain('prefers-reduced-motion', 'saveData', 'pointer: coarse', 'deviceMemory', 'detectWebglSupport')
        ->not->toContain('userAgent', 'hardwareConcurrency')
        ->and($metadata)
        ->toContain('canonical', 'og:title', 'twitter:card', 'application/ld+json')
        ->not->toContain('twitter:site', 'og:image')
        ->and($copy)
        ->toContain('Public interface planned', 'Foundation implemented', 'NativePHP mobile application');
});

test('every editorial public page selects a distinct immersive scene variant', function () {
    $pages = [
        'about.tsx' => 'knowledge',
        'open-source.tsx' => 'system',
        'accessibility.tsx' => 'signal',
        'content-policy.tsx' => 'boundary',
        'copyright-and-takedown.tsx' => 'rights',
    ];

    foreach ($pages as $page => $variant) {
        expect(file_get_contents(resource_path("js/pages/public/{$page}")))
            ->toContain("variant=\"{$variant}\"");
    }
});
