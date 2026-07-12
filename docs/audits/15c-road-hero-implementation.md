# Prompt 15C Road Hero Implementation Audit

## Implemented boundary

- Expanded `RoadHero` into a continuous 940svh journey with four semantic case-file sections and a final conversion chapter while retaining one persistent Canvas through the footer approach.
- Added a modular procedural R3F scene, semantic overlay, loader, controls, capability tiers, fallback composition, and typed rights manifest.
- Extended the existing procedural audio controller with motion-linked engine sound and hero/route lifecycle handling.
- Preserved the pre-existing dirty Prompt 15B worktree and added no package.

## Production bundle measurements

Measured from `npm run build` on 2026-07-13. gzip values below are direct `gzip -c` measurements.

| Artifact | Raw | gzip |
| --- | ---: | ---: |
| Homepage chunk | 17,570 B | 6,540 B |
| Hero scene module | 26,444 B | 8,926 B |
| Shared React Three Fiber / Three chunk | 852,032 B | 223,252 B |
| GSAP | 69,941 B | 27,132 B |
| ScrollTrigger | 42,754 B | 17,407 B |
| Main CSS | 237,572 B | 40,550 B |
| WOFF2 fonts present in build | 166,992 B | Already compressed |
| Model files | 0 B | 0 B |
| CC0 road texture files | 2,321,536 B | JPEG source files |
| Audio files | 0 B | 0 B |

Vite still reports the shared 852,032 B React Three Fiber/Three chunk above its 500 kB warning threshold. The scene and homepage modules are separately lazy-split; the shared renderer and the new 2.32 MB source texture set remain the main transfer weaknesses.

## Runtime measurements and cleanup

Tested in the Codex in-app Chromium browser against Laravel Herd at `http://supernatural.test`, 1440×900 desktop and 390×844 / 320×568 responsive overrides.

- A fresh measured navigation returned in 355 ms; the semantic H1 was found by 396 ms. These are browser-tool upper bounds, not lab Web Vitals.
- The Canvas was observed by 547 ms on the same run.
- A separate cold `curl` response transferred 31,238 B of HTML, with 5.117 s time-to-first-byte and 5.117 s total. This unusually slow one-off Herd response is recorded without normalizing it away.
- Fresh-browser console review contained Vite/React development notices only: no errors, hydration warnings, or failed asset messages.
- The normal homepage now defaults to Full mode and contained exactly one Canvas even when the browser reported reduced motion, matching the owner's explicit direction. No duplicate Canvas was observed.
- Route start/finish pauses and resumes audio; component unmount disconnects the observer, marks the runtime inactive, disables hero activity, reverts the GSAP context, kills the hero's ScrollTrigger, and the shared Lenis controller destroys its singleton.
- The available browser diagnostics did not expose FPS, JavaScript heap, long-task entries, or raw WebGL context counts. Those values are intentionally reported as unavailable rather than invented. No rapidly increasing memory claim is made.
- Automated Web Audio activation remained off in the browser harness after the gesture; the off-state control was captured. The source contract and tests prove opt-in/lifecycle wiring, but audible output still needs a manual owner-browser check.

## Accessibility review

- H1, statement, CTA links, skip link, effects selector, sound toggle, and mix controls are DOM content outside Canvas.
- Canvas and fallback art are decorative; the section has a labelled semantic region.
- Reduced-motion and Save-Data bypass animation and loader; WebGL failure retains the same content.
- Loader has a named dialog, progressbar values, Skip button, session-only completion, and focus restoration to the title.
- Inertia navigation restores focus to `#main-content`; visibility and route lifecycle pause sound.
- Keyboard-reachable controls have visible focus treatments and at least 44px mobile targets. The effects selector and sound labels expose state.
- 390×844, 320×568, 768×1024, desktop, light, dark, reduced, and WebGL-fallback layouts were exercised. The 320px capture retains the semantic title and CTA.
- No flashing sequence is used. Formal WCAG conformance is not claimed; automated 200% browser zoom and a screen-reader session were unavailable.

## Visual review

- Lighting: dark and light states are clearly distinct; dark mids need more separation.
- Vehicle: continuously moving sculpted roadster with a cornering/U-turn beat, zigzag pursuit path, and final braking state; still procedural rather than photoreal.
- Encounters: original ghost, demon, vampire, and wraith geometry emerges opposite each content panel and scales, rotates, and dissolves across impact windows.
- Fog/weather: layered fog, particles, cloud planes, wet-road response, and motion-linked weather are visible; headlight cones need refinement.
- Typography: Cinzel/Cormorant/Special Elite create a clear cinematic editorial hierarchy without copied marks.
- Camera: progress visibly changes follow distance, height, look target, vehicle motion, road movement, and entrance scale.
- Mobile: portrait composition and controls are deliberate, though the browser wheel synthesizer jumped between chapters too coarsely for a precise 50% capture.
- Fallback: intentional, themed CSS composition rather than an error panel.

## Evidence and recording

All screenshots are in `docs/reviews/15c-road-hero/`. The browser tool did not expose video recording. Six numbered motion frames (`motion-00-0ms.png` through `motion-05-2000ms.png`) provide the required substitute sequence.

## Validation status

The final suite passed 317 tests with 1,757 assertions. PHPStan, Pint, ESLint, Prettier, TypeScript, Vite, and Git whitespace checks passed. Vite's shared Three.js chunk-size warning is the only final command warning.
