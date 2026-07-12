<?php

test('road hero asset manifest records rights and a fallback for every asset', function () {
    $manifest = file_get_contents(resource_path('js/features/experience/road-hero/asset-manifest.ts'));

    expect($manifest)
        ->toContain('commercialUsePermitted', 'modificationPermitted', 'repositoryRedistributionPermitted', 'fallback')
        ->toContain('Original repository source', 'OFL-1.1', 'Apache-2.0', 'CC0-1.0', 'Rob Tuytel / Poly Haven')
        ->toContain('generated-spectral-runner', 'Original spectral runner VFX cutout')
        ->not->toContain('Sketchfab', 'Kenney');

    foreach ([
        'asphalt-01-diffuse-1k.jpg' => 739513,
        'asphalt-01-normal-1k.jpg' => 1237059,
        'asphalt-01-roughness-1k.jpg' => 344964,
    ] as $file => $bytes) {
        expect(public_path("media/road-journey/polyhaven/{$file}"))
            ->toBeFile()
            ->and(filesize(public_path("media/road-journey/polyhaven/{$file}")))
            ->toBe($bytes);
    }

    expect(public_path('media/road-journey/generated/spectral-runner-768.png'))
        ->toBeFile();
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
    $road = file_get_contents(resource_path('js/features/experience/road-hero/components/road.tsx'));
    $motion = file_get_contents(resource_path('js/features/experience/road-hero/motion.ts'));

    expect($scroll)
        ->toContain("import('gsap')", "import('gsap/ScrollTrigger')", 'scrub: 0.65', 'context.revert()', 'trigger.kill()', 'experienceAudio.setMotion')
        ->and($camera)
        ->toContain('runtime.current.progress', 'camera.position')
        ->and($vehicle)
        ->toContain('RoundedBox', 'getRoadHeroPose', 'pose.yaw', 'pose.turnArc')
        ->and($road)
        ->toContain('runtime.current.distance +=', 'runtime.current.driveSpeed', 'targetSpeed = 8.5', 'pose.travelDirection', 'asphalt-01-diffuse-1k.jpg')
        ->and($motion)
        ->toContain('Math.PI * turnPhase', 'turnArc * 4.15', 'travelDirection');
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

test('homepage renders a semantic supernatural journey without the retired car scene', function () {
    $welcome = file_get_contents(resource_path('js/pages/welcome.tsx'));

    expect($welcome)
        ->toContain('PublicHead', 'PublicPageIntro', 'PublicArticleSection', 'archive-opens', 'SUPERNATURAL / THE ARCHIVE', 'Spirits leave patterns behind.', 'Demons always want a deal.', 'Every monster has rules.', 'Family is the final protection.')
        ->not->toContain('RoadHero', 'RoadScene', 'Vehicle', 'CreatureEncounters')
        ->not->toContain('WardingMark', 'sigil=', 'data-content-sigil');
});

test('global background uses a clean monochrome image sequence and reduced motion fallback', function () {
    $styles = file_get_contents(resource_path('css/app.css'));
    $backdrop = file_get_contents(resource_path('js/features/experience/public-immersive-backdrop.tsx'));

    expect($styles)
        ->toContain('.cinematic-image-stage', '.cinematic-image-frame', 'filter: grayscale(1) contrast(1.08) brightness(0.9)', '@keyframes cinematic-image-drift', "html[data-experience-visual='reduced'] .cinematic-image-frame")
        ->not->toContain('.cinematic-collage-grid', '.cinematic-collage-tile', 'perspective: 1200px')
        ->and($backdrop)
        ->toContain('currentImage', 'previousImage', '--scene-shift-x', '--scene-shift-y', '--scene-scroll-y', "visualMode === 'reduced'")
        ->not->toContain('RoadScene', 'Canvas', 'WebGL');
});
