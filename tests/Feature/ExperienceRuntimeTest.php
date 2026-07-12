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

test('full cinematic experience is the default and the header has no mode selector', function () {
    $provider = file_get_contents(resource_path('js/features/experience/experience-provider.tsx'));
    $layout = file_get_contents(resource_path('js/layouts/public/public-marketing-layout.tsx'));

    expect($provider)
        ->toContain("useState<ExperiencePreference>('full')")
        ->and($layout)
        ->not->toContain('ExperienceControls');
});

test('experience assets fail closed when required rights metadata is unknown', function () {
    $manifest = file_get_contents(resource_path('experience/assets-manifest.ts'));

    expect($manifest)
        ->toContain('validateExperienceAssets', "hostingPermission === 'unknown'", "commercialUse === 'unknown'", "reviewStatus !== 'approved'")
        ->toContain('OFL-1.1', 'Apache-2.0', 'Original procedural geometry')
        ->not->toContain('anti-possession', 'Men of Letters', 'Impala');
});

test('the immersive scene has a lazy WebGL boundary and complete DOM fallback', function () {
    $hero = file_get_contents(resource_path('js/components/public/archive-hero-scene.tsx'));
    $scene = file_get_contents(resource_path('js/features/experience/night-road-scene.tsx'));
    $welcome = file_get_contents(resource_path('js/pages/welcome.tsx'));

    expect($hero)
        ->toContain('lazy(', 'Suspense', 'SceneErrorBoundary', 'reportWebglFailure', 'archive-hero-light-sweep', 'archive-hero-reticle')
        ->and($scene)
        ->toContain('webglcontextlost', 'frameloop', 'document.hidden', '1800', 'floatingPages', 'AdditiveBlending')
        ->and($welcome)
        ->toContain('homepageChapters.map', 'CinematicPreloader', 'HomepageChoreography', 'HeroSoundControl', 'MediaInterlude');
});

test('homepage choreography uses scrubbed chapter and media animation', function () {
    $choreography = file_get_contents(resource_path('js/features/experience/homepage-choreography.tsx'));

    expect($choreography)
        ->toContain("'.archive-hero-title > span'", "'--scene-progress'", "'.media-reel img'", 'scrub: 1');
});

test('route transitions and smooth scroll include cleanup and reduced experience handling', function () {
    $provider = file_get_contents(resource_path('js/features/experience/experience-provider.tsx'));
    $scroll = file_get_contents(resource_path('js/features/experience/smooth-scroll-controller.ts'));

    expect($provider)
        ->toContain("router.on('start'", "router.on('finish'", "visualMode !== 'reduced'")
        ->and($scroll)
        ->toContain('lenis.destroy()', 'gsap.ticker.remove', 'ScrollTrigger.getAll()', 'form, dialog');
});
