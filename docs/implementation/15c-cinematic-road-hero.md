# Prompt 15C Cinematic Road Hero

## Scene design

The homepage opening is a single, scroll-driven road journey. A procedural roadster begins in a wet conifer corridor, advances through fog and weather, and approaches an original illuminated archive entrance. The title and calls to action are ordinary semantic DOM layered above the decorative Canvas.

The implementation is isolated under `resources/js/features/experience/road-hero/`. `RoadScene` composes `Atmosphere`, `Road`, `Forest`, `ArchiveEntrance`, `Vehicle`, `Weather`, and `HeroCamera`. The Canvas is lazy, protected by the existing scene error boundary, and replaced by the same CSS composition for reduced motion, missing WebGL, load failure, or the fallback quality tier.

## Dependencies and assets

No dependency was added. The hero uses the installed React Three Fiber, Three.js, Drei, GSAP/ScrollTrigger, Lenis, and Web Audio stack. Three, React Three Fiber, Drei, Lenis, and Howler are MIT licensed; GSAP uses its standard no-charge licence. Howler remains installed but is not loaded by this hero.

All visual and audio scene inputs are original procedural source. No model, texture, HDRI, image, audio file, episode, trailer, official logo, official symbol, or franchise recording was downloaded. Fontsource supplies Cinzel Decorative and Cormorant Garamond under OFL-1.1, Special Elite under Apache-2.0, and Instrument Sans under OFL-1.1. Full rights metadata and rejected candidates are in `docs/media/15c-road-hero-assets.md` and the typed asset manifest.

## Animation and audio

One ScrollTrigger owns the section progress. Progress 0–0.30 establishes the departure, 0.30–0.65 increases speed and changes copy, and 0.65–1.00 closes on the luminous entrance. Camera position, road recycling, wheel rotation, weather velocity, light intensity, entrance scale, and audio engine pitch consume the same mutable runtime. Cleanup reverts the GSAP context and kills only triggers attached to this section.

The loader combines `document.fonts.ready` and the Canvas `onCreated` callback, exposes a real progress bar, supports Skip, bypasses reduced mode, and records completion in session storage. Completion focuses the hero title.

Audio is generated entirely with Web Audio: a filtered drone, synthesized wind, a low engine oscillator, and short interface tones. It is muted by default and requires the sound button. Ambient and effects gains have separate controls. Visibility, hero intersection, and Inertia route changes pause or resume the graph; Reduced and Silent modes disable it.

## Appearance, mobile, and fallback

Dark appearance uses a near-black blue-green forest, moonlight, red tail lamps, cyan entrance light, and stronger haze. Light appearance uses overcast dawn values with higher ambient fill while preserving contrast. The scene changes material and lighting values without recreating the public page.

Automatic mode maps capable desktop hardware to High, constrained desktop to Medium, narrow/coarse devices to Low, and reduced motion, Save-Data, WebGL failure, or explicit fallback to the CSS scene. Mobile uses a 220svh journey, a tighter portrait camera, simplified effects, wrapped 44px controls, and mobile navigation. The fallback retains road, forest, car, fog, entrance, title, CTA, controls, and theme differentiation without Canvas.

## Known visual weaknesses and next improvement

The procedural roadster reads as an original muscle-car silhouette but remains visibly low-detail around the roof, wheel arches, glass, and rear fascia. The forest is intentionally graphic rather than photoreal. Dark-mode mids are slightly crushed, the headlight/fog cones can read as translucent geometry, and the archive entrance is still a simple monolith. The exact next visual pass should improve the roadster silhouette and material breakup, then replace the headlight cones with a softer depth-aware volumetric treatment while keeping the current rights-clear procedural boundary.

