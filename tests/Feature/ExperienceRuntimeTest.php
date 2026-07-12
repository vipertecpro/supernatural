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

test('the road hero has a lazy WebGL boundary and complete DOM fallback', function () {
    $hero = file_get_contents(resource_path('js/features/experience/road-hero/components/road-hero.tsx'));
    $scene = file_get_contents(resource_path('js/features/experience/road-hero/components/road-scene.tsx'));
    $fallback = file_get_contents(resource_path('js/features/experience/road-hero/components/hero-fallback.tsx'));
    $welcome = file_get_contents(resource_path('js/pages/welcome.tsx'));

    expect($hero)
        ->toContain('lazy(', 'Suspense', 'SceneErrorBoundary', 'reportWebglFailure', 'HeroFallback', 'HeroLoader')
        ->and($scene)
        ->toContain('webglcontextlost', 'powerPreference', 'Road', 'Forest', 'Vehicle', 'Weather')
        ->and($fallback)
        ->toContain('road-hero-fallback-sky', 'road-hero-fallback-road', 'road-hero-fallback-car', 'data-fallback-reason')
        ->and($welcome)
        ->toContain('RoadHero', 'archive-opens')
        ->not->toContain('homepageChapters.map', 'MediaInterlude');
});

test('editorial routes share a shader-backed three dimensional scene and gsap choreography', function () {
    $layout = file_get_contents(resource_path('js/layouts/public/public-marketing-layout.tsx'));
    $backdrop = file_get_contents(resource_path('js/features/experience/public-immersive-backdrop.tsx'));
    $scene = file_get_contents(resource_path('js/features/experience/ambient-archive-scene.tsx'));
    $choreography = file_get_contents(resource_path('js/features/experience/public-page-choreography.tsx'));

    expect($layout)
        ->toContain('PublicImmersiveBackdrop')
        ->and($backdrop)
        ->toContain('lazy(', 'mounted && webglEnabled', 'SceneErrorBoundary')
        ->and($scene)
        ->toContain('fragmentShader', 'shaderMaterial', 'AdditiveBlending', '1100', 'webglcontextlost')
        ->and($choreography)
        ->toContain("import('gsap')", "import('gsap/ScrollTrigger')", 'scrub: 1.2', '[data-immersive-section]', 'revealFallback', 'clearProps:');
});

test('authentication and onboarding shells inherit the immersive scene with responsive panels', function () {
    $auth = file_get_contents(resource_path('js/layouts/auth/auth-simple-layout.tsx'));
    $onboarding = file_get_contents(resource_path('js/layouts/onboarding/onboarding-layout.tsx'));
    $styles = file_get_contents(resource_path('css/app.css'));

    expect($auth)
        ->toContain('PublicImmersiveBackdrop', 'immersive-auth-story', 'immersive-auth-panel')
        ->and($onboarding)
        ->toContain('PublicImmersiveBackdrop', 'immersive-onboarding-grid', 'onboarding-content-panel')
        ->and($styles)
        ->toContain('@media (max-width: 1023px)', '@keyframes auth-panel-enter', '@keyframes onboarding-panel-enter');
});

test('authenticated application pages retain task clarity inside the shared cinematic layer', function () {
    $layout = file_get_contents(resource_path('js/layouts/fan/fan-layout.tsx'));
    $frame = file_get_contents(resource_path('js/components/shell/page-frame.tsx'));

    expect($layout)
        ->toContain('PublicImmersiveBackdrop', 'PublicPageChoreography', 'immersive-app-inset', 'immersive-app-main')
        ->and($frame)
        ->toContain('app-page-header', 'app-page-section', 'data-immersive-section');
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
