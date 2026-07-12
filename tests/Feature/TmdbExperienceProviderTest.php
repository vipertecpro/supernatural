<?php

use App\Domain\Media\Services\TmdbExperienceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

test('TMDB stays disabled without explicit terms and server configuration', function () {
    config()->set('public-site.tmdb.token');
    config()->set('public-site.tmdb.series_id');
    config()->set('public-site.tmdb.terms_accepted', false);
    Http::fake();

    $result = app(TmdbExperienceProvider::class)->images();

    expect($result)->toBe([
        'enabled' => false,
        'attribution' => null,
        'notice' => null,
        'images' => [],
    ]);
    Http::assertNothingSent();

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('experienceMedia.tmdb.enabled', false)
            ->missing('experienceMedia.tmdb.token'));
});

test('TMDB returns metadata with attribution when every usage gate is satisfied', function () {
    config()->set('public-site.tmdb', [
        'token' => 'server-only-token',
        'series_id' => '1622',
        'image_base_url' => 'https://image.tmdb.org/t/p',
        'terms_accepted' => true,
        'commercial_licensed' => false,
    ]);
    config()->set('public-site.commercial', false);
    Http::fake([
        'api.themoviedb.org/3/tv/1622/images*' => Http::response([
            'backdrops' => [[
                'file_path' => '/approved-backdrop.jpg',
                'width' => 1280,
                'height' => 720,
            ]],
        ]),
    ]);

    $result = app(TmdbExperienceProvider::class)->images();

    expect($result['enabled'])->toBeTrue()
        ->and($result['images'])->toHaveCount(1)
        ->and($result['images'][0]['src'])->toBe('https://image.tmdb.org/t/p/w780/approved-backdrop.jpg')
        ->and($result['attribution'])->toContain('TMDB')
        ->and($result['notice'])->toContain('not endorsed or certified');

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer server-only-token'));
});

test('TMDB rejects arbitrary image hosts and commercial use without a licence', function (bool $commercial, bool $licensed, string $baseUrl) {
    config()->set('public-site.tmdb', [
        'token' => 'server-only-token',
        'series_id' => '1622',
        'image_base_url' => $baseUrl,
        'terms_accepted' => true,
        'commercial_licensed' => $licensed,
    ]);
    config()->set('public-site.commercial', $commercial);
    Http::fake();

    expect(app(TmdbExperienceProvider::class)->images()['enabled'])->toBeFalse();
    Http::assertNothingSent();
})->with([
    'arbitrary host' => [false, false, 'https://images.example.test'],
    'unlicensed commercial site' => [true, false, 'https://image.tmdb.org/t/p'],
]);
