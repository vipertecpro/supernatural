<?php

test('experience runtime defines every mode and conservative capability override', function () {
    $types = file_get_contents(resource_path('js/features/experience/types.ts'));
    $resolver = file_get_contents(resource_path('js/features/experience/capability-resolver.ts'));
    $provider = file_get_contents(resource_path('js/features/experience/experience-provider.tsx'));

    expect($types)
        ->toContain("'full'", "'balanced'", "'reduced'", "'silent'")
        ->and($resolver)
        ->toContain('prefers-reduced-motion: reduce', 'saveData', 'coarsePointer', 'deviceMemory', 'detectWebglSupport')
        ->and($provider)
        ->toContain("useState<ExperiencePreference>('full')", 'sessionStorage', 'visibilitychange', 'startSmoothScroll', 'stopSmoothScroll')
        ->not->toContain("localStorage.getItem('archive-experience-mode')")
        ->not->toContain('userAgent', 'hardwareConcurrency');
});

test('experience sound is gesture gated muted by default and pauses while hidden', function () {
    $audio = file_get_contents(resource_path('js/features/experience/audio-controller.ts'));
    $controls = file_get_contents(resource_path('js/features/experience/hero-sound-control.tsx'));

    expect($audio)
        ->toContain('async enable()', 'this.enabled = false', 'createOscillator', 'createBufferSource', 'suspend()', 'resume()')
        ->not->toContain('.mp3', '.wav', '.ogg')
        ->and($controls)
        ->toContain('Enter with sound', 'Atmosphere', 'Interface');
});

test('full cinematic experience is the default and the public interface has no mode selector', function () {
    $provider = file_get_contents(resource_path('js/features/experience/experience-provider.tsx'));
    $layout = file_get_contents(resource_path('js/layouts/public/public-marketing-layout.tsx'));
    $heroControls = file_get_contents(resource_path('js/features/experience/road-hero/components/hero-controls.tsx'));

    expect($provider)
        ->toContain("useState<ExperiencePreference>('full')")
        ->and($layout)
        ->not->toContain('ExperienceControls')
        ->and($heroControls)
        ->not->toContain('Effects quality', '<select', 'ExperiencePreference');
});

test('experience assets fail closed when required rights metadata is unknown', function () {
    $manifest = file_get_contents(resource_path('experience/assets-manifest.ts'));

    expect($manifest)
        ->toContain('validateExperienceAssets', "hostingPermission === 'unknown'", "commercialUse === 'unknown'", "reviewStatus !== 'approved'")
        ->toContain('OFL-1.1', 'Apache-2.0', 'Original procedural geometry')
        ->not->toContain('anti-possession', 'Men of Letters', 'Impala');
});

test('the homepage replaces the road WebGL scene with an editorial supernatural journey', function () {
    $welcome = file_get_contents(resource_path('js/pages/welcome.tsx'));
    $layout = file_get_contents(resource_path('js/layouts/public/public-marketing-layout.tsx'));
    $backdrop = file_get_contents(resource_path('js/features/experience/public-immersive-backdrop.tsx'));
    $styles = file_get_contents(resource_path('css/app.css'));

    expect($welcome)
        ->toContain('PublicPageIntro', 'PublicArticleSection', 'CastShowcase', 'href="#cast"', 'Meet the cast', 'archive-opens', 'archive-story-ribbon', 'archive-story-marquee', 'archive-story-track', 'archive-closing-call', 'Spirits leave patterns behind.', 'Every monster has rules.', 'supernaturalMinimalImages[58]')
        ->not->toContain('RoadHero', 'road-hero', 'Canvas', 'WebGL')
        ->and($layout)
        ->toContain('<PublicImmersiveBackdrop url={currentUrl} />')
        ->not->toContain('!isHomepage')
        ->and($backdrop)
        ->toContain('currentImage', 'previousImage', 'cinematic-image-current', 'cinematic-image-stage', 'cinematic-image-frame', 'https://skiper-ui.com/components')
        ->not->toContain('cinematic-collage-grid', 'data-collage-tile')
        ->not->toContain('AmbientArchiveScene', 'SceneErrorBoundary', 'webglEnabled')
        ->and($styles)
        ->toContain('.archive-story-ribbon', '.archive-story-marquee', '.archive-story-track', '.public-article-media', '.archive-closing-call', '@keyframes archive-story-track');
});

test('the homepage cast index uses local licensed portraits and keyboard accessible interaction', function () {
    $cast = file_get_contents(resource_path('js/features/experience/public-cast.ts'));
    $component = file_get_contents(resource_path('js/components/public/cast-showcase.tsx'));
    $styles = file_get_contents(resource_path('css/app.css'));

    foreach ([
        'jared-padalecki.jpg',
        'jensen-ackles.jpg',
        'misha-collins.jpg',
        'mark-sheppard.jpg',
        'jim-beaver.jpg',
        'alexander-calvert.jpg',
        'ruth-connell.jpg',
        'kim-rhodes.jpg',
        'felicia-day.jpg',
        'rob-benedict.jpg',
    ] as $portrait) {
        $path = public_path("media/cast/{$portrait}");

        expect($path)
            ->toBeFile()
            ->and(filesize($path))
            ->toBeGreaterThan(10_000);
    }

    expect(substr_count($cast, "\n        actor: '"))->toBe(10);

    expect($cast)
        ->toContain('Sam Winchester', 'Dean Winchester', 'Castiel', 'Crowley', 'Bobby Singer', 'Jack Kline', 'Rowena MacLeod', 'Jody Mills', 'Charlie Bradbury', 'Chuck Shurley', "code: 'SW'", 'summary:', 'commons.wikimedia.org', 'CC BY-SA')
        ->and($component)
        ->toContain('useState(0)', 'cast-showcase-portrait', 'cast-showcase-index', 'cast-showcase-code', 'cast-showcase-summary', 'onMouseEnter', 'onFocus', 'aria-pressed', 'Portrait credits')
        ->and($styles)
        ->toContain('.cast-showcase', '.cast-showcase-portrait', '.cast-showcase-active-copy', '.cast-showcase-summary', 'position: sticky', '.cast-showcase-index button[data-active=', '@keyframes cast-portrait-enter', '@keyframes cast-copy-enter');
});

test('editorial routes share a layered collage and gsap choreography', function () {
    $layout = file_get_contents(resource_path('js/layouts/public/public-marketing-layout.tsx'));
    $backdrop = file_get_contents(resource_path('js/features/experience/public-immersive-backdrop.tsx'));
    $choreography = file_get_contents(resource_path('js/features/experience/public-page-choreography.tsx'));
    $intro = file_get_contents(resource_path('js/components/public/public-page-intro.tsx'));
    $styles = file_get_contents(resource_path('css/app.css'));
    $publicPages = collect(glob(resource_path('js/pages/public/*.tsx')))
        ->map(fn (string $path): string => file_get_contents($path))
        ->implode("\n");

    expect($layout)
        ->toContain('PublicImmersiveBackdrop')
        ->and($backdrop)
        ->toContain("window.addEventListener('pointermove'", "window.addEventListener('scroll'", "visualMode === 'reduced'", '--scene-scroll-y')
        ->and($choreography)
        ->toContain("import('gsap')", "import('gsap/ScrollTrigger')", 'scrub: 1.2', '[data-immersive-section]', '.public-article-media', "clipPath: 'inset(0 0 100% 0)'", 'revealFallback', 'clearProps:')
        ->and($intro)
        ->toContain('PublicPageIntro', 'PublicArticleSection', 'resolvePublicCollageImages', 'immersive-page-hero-media', 'ARCHIVE / VISUAL RECORD', 'public-article-metadata', 'ARCHIVE RECORD', 'data-immersive-section', 'data-has-media={Boolean(image)}', 'public-article-media', 'loading="lazy"')
        ->not->toContain('WardingMark', 'PageGlyph', 'data-content-sigil', 'public-article-sigil')
        ->and($publicPages)
        ->not->toContain('sigil="', 'WardingMark');

    expect(substr_count($publicPages, '<PublicArticleSection'))->toBeGreaterThanOrEqual(20);

    expect($styles)
        ->toContain('.immersive-page-hero-media', '.public-article-metadata', '.public-content-shell .public-article-section h2::after');
});

test('invented sigils and warding marks are absent from the interface and dormant scenes', function () {
    $sources = collect([
        ...glob(resource_path('js/**/*.tsx')),
        ...glob(resource_path('js/**/**/*.tsx')),
        ...glob(resource_path('js/**/**/**/*.tsx')),
    ])->unique()->map(fn (string $path): string => file_get_contents($path))->implode("\n");
    $styles = file_get_contents(resource_path('css/app.css'));
    $manifest = file_get_contents(resource_path('js/features/experience/road-hero/asset-manifest.ts'));

    expect($sources)
        ->not->toContain('WardingMark', 'WardingField', 'immersive-auth-sigil', 'data-content-sigil', 'public-article-sigil')
        ->and($styles)
        ->not->toContain('warding-mark', 'road-journey-ward', 'immersive-glyph', 'public-article-sigil', 'immersive-auth-sigil', 'auth-sigil-turn')
        ->and($manifest)
        ->not->toContain('procedural-warding-system', 'semantic-warding-marks');
});

test('public editorial references are local monochrome and form one global collage', function () {
    $images = file_get_contents(resource_path('js/features/experience/public-collage-images.ts'));
    $commons = json_decode(
        file_get_contents(resource_path('js/features/experience/commons-collage-images.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $backdrop = file_get_contents(resource_path('js/features/experience/public-immersive-backdrop.tsx'));
    $styles = file_get_contents(resource_path('css/app.css'));

    foreach ([
        'brothers-impala-night.jpg' => 25213,
        'hunters-stargazing-illustration.jpg' => 36153,
        'falling-stars-hunter.jpg' => 54219,
        'young-hunters.jpg' => 40344,
        'celestial-hunter-art.jpg' => 249244,
        'crowned-hunter-art.jpg' => 72314,
    ] as $file => $bytes) {
        $path = public_path("media/editorial/pinterest/{$file}");

        expect($path)
            ->toBeFile()
            ->and(filesize($path))
            ->toBe($bytes);
    }

    expect($commons)->toHaveCount(60);

    foreach ($commons as $image) {
        expect($image['rightsStatus'])
            ->toBe('verified-reusable')
            ->and($image['license'])
            ->toMatch('/^(CC0|CC BY|CC BY-SA|Public domain)/')
            ->and(public_path(ltrim($image['src'], '/')))
            ->toBeFile()
            ->and(filesize(public_path(ltrim($image['src'], '/'))))
            ->toBeGreaterThan(1000);
    }

    expect($images)
        ->toContain("rightsStatus: 'user-directed-reference'", 'brothers-impala-night', 'hunters-stargazing-illustration', 'falling-stars-hunter', 'young-hunters', 'celestial-hunter-art', 'crowned-hunter-art', 'supernaturalMinimalImages', 'routeOffset', 'collageVariantOrder')
        ->and($backdrop)
        ->toContain('currentImage', 'previousImage', 'window.setInterval', '10000', 'new Image()', 'Image credits', 'Motion direction inspired by', 'routeImages.map', 'loading="eager"', 'decoding="async"')
        ->and($styles)
        ->toContain('filter: grayscale(1) contrast(1.08) brightness(0.9)', '.cinematic-image-stage', '.cinematic-image-frame', '.cinematic-image-scrim', '.cinematic-collage-credits', '.cinematic-image-previous', 'clip-path: inset(0 0 0 100%)', '@keyframes cinematic-image-drift', '@keyframes cinematic-image-enter')
        ->not->toContain('.cinematic-collage-grid', '.cinematic-collage-tile', '.immersive-page-intro::before');

    expect(substr_count($images, "rightsStatus: 'user-directed-reference'"))->toBe(7);
});

test('authentication and onboarding shells inherit the immersive scene with responsive panels', function () {
    $auth = file_get_contents(resource_path('js/layouts/auth/auth-simple-layout.tsx'));
    $onboarding = file_get_contents(resource_path('js/layouts/onboarding/onboarding-layout.tsx'));
    $styles = file_get_contents(resource_path('css/app.css'));
    $login = file_get_contents(resource_path('js/pages/auth/login.tsx'));

    expect($auth)
        ->toContain('PublicImmersiveBackdrop', 'immersive-auth-story', 'immersive-auth-panel')
        ->and($login)
        ->toContain('FieldGroup', 'FieldLabel', 'auth-motion-form', 'aria-invalid')
        ->and($onboarding)
        ->toContain('PublicImmersiveBackdrop', 'immersive-onboarding-grid', 'onboarding-content-panel')
        ->and($styles)
        ->toContain('@media (max-width: 1023px)', '@keyframes auth-panel-enter', '@keyframes onboarding-panel-enter');
});

test('authenticated application pages retain task clarity inside the shared cinematic layer', function () {
    $layout = file_get_contents(resource_path('js/layouts/fan/fan-layout.tsx'));
    $frame = file_get_contents(resource_path('js/components/shell/page-frame.tsx'));
    $dashboard = file_get_contents(resource_path('js/pages/dashboard.tsx'));

    expect($layout)
        ->toContain('PublicImmersiveBackdrop', 'PublicPageChoreography', 'immersive-app-inset', 'immersive-app-main')
        ->and($frame)
        ->toContain('app-page-header', 'app-page-section', 'data-immersive-section')
        ->and($dashboard)
        ->toContain('dashboard-motion-grid', 'dashboard-motion-card');
});

test('three dimensional scenes adapt quality only after sustained frame pressure', function () {
    $governor = file_get_contents(resource_path('js/features/experience/scene-performance-governor.tsx'));
    $ambient = file_get_contents(resource_path('js/features/experience/ambient-archive-scene.tsx'));
    $hero = file_get_contents(resource_path('js/features/experience/night-road-scene.tsx'));

    expect($governor)
        ->toContain('elapsed.current < 5', 'framesPerSecond < 42', "quality === 'low'", 'document.hidden')
        ->and($ambient)
        ->toContain('ScenePerformanceGovernor', 'applyExperienceQualityDrops', 'qualityDrops')
        ->and($hero)
        ->toContain('ScenePerformanceGovernor', 'applyExperienceQualityDrops', 'qualityDrops');
});

test('public navigation prefetches likely inertia destinations', function () {
    $layout = file_get_contents(resource_path('js/layouts/public/public-marketing-layout.tsx'));
    $footer = file_get_contents(resource_path('js/components/public/public-footer.tsx'));

    expect($layout)
        ->toContain('prefetch')
        ->and($footer)
        ->toContain('prefetch');
});

test('homepage choreography uses scrubbed chapter and media animation', function () {
    $choreography = file_get_contents(resource_path('js/features/experience/homepage-choreography.tsx'));

    expect($choreography)
        ->toContain("'.archive-hero-title > span'", "'--scene-progress'", "'.media-reel img'", 'scrub: 1', 'heroRevealFallback', 'clearProps:');
});

test('route transitions and smooth scroll include cleanup and reduced experience handling', function () {
    $provider = file_get_contents(resource_path('js/features/experience/experience-provider.tsx'));
    $scroll = file_get_contents(resource_path('js/features/experience/smooth-scroll-controller.ts'));

    expect($provider)
        ->toContain("router.on('start'", "router.on('finish'", "visualMode !== 'reduced'")
        ->and($scroll)
        ->toContain('lenis.destroy()', 'gsap.ticker.remove', 'ScrollTrigger.getAll()', 'form, dialog');
});
