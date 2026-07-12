<?php

test('road hero asset manifest records rights and a fallback for every asset', function () {
    $manifest = file_get_contents(resource_path('js/features/experience/road-hero/asset-manifest.ts'));

    expect($manifest)
        ->toContain('commercialUsePermitted', 'modificationPermitted', 'repositoryRedistributionPermitted', 'fallback')
        ->toContain('Original repository source', 'OFL-1.1', 'Apache-2.0')
        ->not->toContain('Sketchfab', 'Poly Haven', 'Kenney');
});

test('road hero loader waits for fonts and scene readiness and remains skippable', function () {
    $loader = file_get_contents(resource_path('js/features/experience/road-hero/components/hero-loader.tsx'));
    $provider = file_get_contents(resource_path('js/features/experience/experience-provider.tsx'));

    expect($loader)
        ->toContain('document.fonts.ready', 'sceneReady', 'role="progressbar"', 'Skip intro', 'visualMode === \'reduced\'')
        ->and($provider)
        ->toContain('sessionStorage', "'#road-hero-title'", 'completeIntro');
});

test('road hero scroll runtime updates cinematic state and cleans up its own triggers', function () {
    $scroll = file_get_contents(resource_path('js/features/experience/road-hero/hooks/use-road-hero-scroll.ts'));
    $camera = file_get_contents(resource_path('js/features/experience/road-hero/components/hero-camera.tsx'));
    $vehicle = file_get_contents(resource_path('js/features/experience/road-hero/components/vehicle.tsx'));

    expect($scroll)
        ->toContain("import('gsap')", "import('gsap/ScrollTrigger')", 'scrub: 0.65', 'context.revert()', 'trigger.kill()', 'experienceAudio.setMotion')
        ->and($camera)
        ->toContain('runtime.current.progress', 'camera.position')
        ->and($vehicle)
        ->toContain('RoundedBox', 'runtime.current.progress', 'runtime.current.velocity');
});

test('road hero capability tiers preserve reduced motion save data and WebGL fallbacks', function () {
    $resolver = file_get_contents(resource_path('js/features/experience/capability-resolver.ts'));
    $hero = file_get_contents(resource_path('js/features/experience/road-hero/components/road-hero.tsx'));

    expect($resolver)
        ->toContain('prefers-reduced-motion: reduce', 'saveData', 'pointer: coarse', 'deviceMemory', "preference !== 'automatic'", "reviewMode !== 'fallback'")
        ->and($hero)
        ->toContain("visualMode === 'reduced'", "quality !== 'fallback'", 'fallbackReason', "'webgl'");
});

test('road hero audio is muted by default gesture gated and lifecycle aware', function () {
    $audio = file_get_contents(resource_path('js/features/experience/audio-controller.ts'));
    $controls = file_get_contents(resource_path('js/features/experience/road-hero/components/hero-controls.tsx'));
    $provider = file_get_contents(resource_path('js/features/experience/experience-provider.tsx'));

    expect($audio)
        ->toContain('private enabled = false', 'async enable()', 'setMotion(', 'setHeroActive(', 'suspend()', 'resume()')
        ->and($controls)
        ->toContain('Enable ambient sound', 'Mute ambient sound', 'audioUnavailable')
        ->not->toContain('Effects quality', '<select')
        ->and($provider)
        ->toContain("router.on('start'", 'experienceAudio.pause()', "router.on('finish'", 'experienceAudio.resume()');
});

test('homepage renders only the semantic road hero and its transition marker', function () {
    $welcome = file_get_contents(resource_path('js/pages/welcome.tsx'));
    $overlay = file_get_contents(resource_path('js/features/experience/road-hero/components/hero-overlay.tsx'));

    expect($welcome)
        ->toContain('PublicHead', 'RoadHero', 'archive-opens')
        ->not->toContain('homepageChapters', 'MediaInterlude', 'NarrativeSection')
        ->and($overlay)
        ->toContain('<h1', 'road-hero-title', 'Begin the journey', 'About the project', 'Skip the road introduction');
});
