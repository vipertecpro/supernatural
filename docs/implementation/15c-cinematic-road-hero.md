# Prompt 15C Cinematic Road Hero

## Scene design

The homepage is one continuous, scroll-driven road journey. A procedural roadster begins in a wet conifer corridor beneath an original atmospheric omen, executes a cornering/U-turn beat, then drives a zigzag pursuit through semantic case-file chapters for spirits, demons, vampires, and unknown entities before braking at the footer approach. The title, case files, and calls to action are ordinary semantic DOM layered above the decorative Canvas.

The implementation is isolated under `resources/js/features/experience/road-hero/`. `RoadScene` composes `Atmosphere`, `Road`, `Forest`, `ArchiveEntrance`, `Vehicle`, `Weather`, and `HeroCamera`. The Canvas is lazy, protected by the existing scene error boundary, and replaced by the same CSS composition for reduced motion, missing WebGL, load failure, or the fallback quality tier.

## Dependencies and assets

No dependency was added. The hero uses the installed React Three Fiber, Three.js, Drei, GSAP/ScrollTrigger, Lenis, and Web Audio stack. Three, React Three Fiber, Drei, Lenis, and Howler are MIT licensed; GSAP uses its standard no-charge licence. Howler remains installed but is not loaded by this hero.

Vehicle, forest, weather, omen, and creature geometry remain original procedural source. The road uses three 1K `Asphalt 01` PBR maps downloaded from Poly Haven under CC0 1.0; exact files, sizes, hashes, source, and fallback metadata are recorded in the asset audit and typed manifest. The supplied Pinterest/CW promotional composition was inspected as mood reference only and is not stored in the repository. No model, HDRI, episode, trailer, official logo, official symbol, soundtrack, or franchise recording was downloaded. Fontsource supplies Cinzel Decorative and Cormorant Garamond under OFL-1.1, Special Elite under Apache-2.0, and Instrument Sans under OFL-1.1.

## Animation and audio

One ScrollTrigger owns the full journey progress. Progress 0–0.18 establishes the continuously moving departure, 0.18–0.30 drives the cornering/U-turn and short stop, 0.30–0.94 drives the zigzag pursuit and four collision/dissolve encounters, and 0.94–1.00 brakes the car at the footer approach. Camera position, lateral vehicle path, road/forest recycling, wheel rotation, weather velocity, encounter visibility, impact dissolution, entrance scale, and audio engine pitch consume the same mutable runtime. Cleanup reverts the GSAP context and kills only triggers attached to this section.

The loader combines `document.fonts.ready` and the Canvas `onCreated` callback, exposes a real progress bar, supports Skip, bypasses reduced mode, and records completion in session storage. Completion focuses the hero title.

Audio is generated entirely with Web Audio: a filtered drone, synthesized wind, a low engine oscillator, and short interface tones. It is muted by default and requires the sound button. Ambient and effects gains have separate controls. Visibility, hero intersection, and Inertia route changes pause or resume the graph; Reduced and Silent modes disable it.

## Appearance, mobile, and fallback

Dark appearance uses a near-black blue-green forest, moonlight, red tail lamps, cyan entrance light, and stronger haze. Light appearance uses overcast dawn values with higher ambient fill while preserving contrast. The scene changes material and lighting values without recreating the public page.

Automatic mode maps capable desktop hardware to High, constrained desktop to Medium, narrow/coarse devices to Low, and reduced motion, Save-Data, WebGL failure, or explicit fallback to the CSS scene. Mobile uses a 220svh journey, a tighter portrait camera, simplified effects, wrapped 44px controls, and mobile navigation. The fallback retains road, forest, car, fog, entrance, title, CTA, controls, and theme differentiation without Canvas.

## Known visual weaknesses and next improvement

The procedural roadster now has separate rear quarters, trunk, fascia, exhausts, glass, wheels, and improved material breakup, but it remains less detailed than a production automotive model. The CC0 asphalt surface is realistic; forest and creatures deliberately remain stylized geometry so collision dissolution stays lightweight and rights-clear. Dark-mode mids can still be lifted, creature silhouettes need another anatomical/detail pass, and the archive entrance remains a simple monolith. The exact next visual pass should replace the procedural vehicle and creature meshes only when exact redistributable model licences and optimized web formats are confirmed.
