# Prompt 15B implementation audit

Date: 2026-07-13

## Implemented

- Central Full/Balanced/Reduced/Silent experience runtime with High/Medium/Low/Fallback tiers.
- Session-scoped cinematic loader, Skip control, and focus restoration.
- Inertia View Transition requests plus route lifecycle fallback overlay.
- Lenis and ScrollTrigger synchronization with cleanup.
- Lazy procedural R3F night-road/archive hero, adaptive DPR/particles, pause behavior, context-loss handling, error boundary, and CSS/SVG fallback.
- GSAP homepage choreography and original SVG/CSS evidence, journey, graph, signal, document, and bunker visuals.
- Cinematic public navigation, full-width mobile archive menu, sound and experience controls.
- Original procedural Web Audio soundscape; muted by default.
- Fail-closed TypeScript asset-rights manifest.
- Server-side, terms-gated TMDB metadata provider with attribution.
- Rights-reviewed, official-channel, click-to-load YouTube presentation.
- CSP and Permissions Policy.
- Self-hosted open-font system.
- Focused Pest contracts for runtime, rights, provider configuration, token privacy, and public response security headers.

## Bundle result

Latest measured build before final validation:

- Homepage: 29.67 kB raw / 9.91 kB gzip.
- Lenis: 18.31 kB raw / 5.29 kB gzip.
- ScrollTrigger: 42.75 kB raw / 17.55 kB gzip.
- GSAP: 69.94 kB raw / 27.41 kB gzip.
- Lazy 3D: approximately 871 kB raw / 233 kB gzip.
- Application CSS: approximately 198 kB raw / 32.6 kB gzip.
- No eager audio or iframe payload.

The lazy 3D chunk produces Vite's generic 500 kB raw warning. It remains isolated, loads only above the fallback tier, and stays below the project 250 kB gzip review budget.

## Browser review

In-app Chromium review covered 320×568, 390×844, 768×1024, 1024×768, and 1440×900. All sizes retained semantic headings/main content and had no horizontal overflow. Reduced mode rendered zero Canvas elements. Emulated standard-motion Full mode resolved High quality and rendered one lazy Canvas. Intro Skip, experience settings, light mode, and dark mode were exercised. TMDB and video fallbacks rendered without empty boxes. Final console findings are recorded after the last clean run.

## Known limits

- No rights-cleared local sound files or approved production external video records were supplied, so those paths remain dormant and tested through contracts/fallbacks.
- No TMDB token or terms acknowledgement was supplied; real provider networking was tested with Laravel HTTP fakes, not a production credential.
- Hardware FPS claims are intentionally omitted. The tested desktop browser used the available local Mac environment; exact GPU model was not exposed or collected.
- The original Prompt 15 page copy still describes future Prompt 16 interfaces honestly. No future domain route was created.

## Final validation record

- `php artisan test --compact`: 303 tests, 1,603 assertions, passing.
- `vendor/bin/phpstan analyse`: zero errors.
- `vendor/bin/pint --format agent` and `vendor/bin/pint --test`: passing.
- `npm run lint` and `npm run lint:check`: passing after fixing synchronous effect state, deterministic particle generation, and unused error-boundary parameters found by the first lint pass.
- `npm run format` and `npm run format:check`: passing.
- `npm run types:check`: passing.
- `npm run build`: passing; the intentionally isolated 3D raw-size warning remains.
- `composer validate --strict`, `composer audit --no-interaction`, and `npm audit`: passing with zero advisories.
- `php artisan route:list -v`, `php artisan config:cache`, and `php artisan route:cache`: passing.
- `git diff --check`: passing.

Intermediate failures were retained rather than suppressed: PHPStan first found five concrete type issues, all corrected; ESLint first found six React purity/effect/unused-parameter issues, all corrected. Running the suite after `config:cache` caused 29 CSRF/auth failures because cached local configuration overrode PHPUnit's testing environment; `config:clear` restored the intended test environment. One existing continue-watching test then failed once nondeterministically, passed in isolation, and the immediate final full suite passed 303/303. The in-app browser exposes no Web Audio API, so the unavailable-audio fallback stayed muted; sound architecture is covered by contracts and remains opt-in on supporting browsers.

Final measured bundles: homepage 29.67 kB raw / 9.91 kB gzip; Lenis 18.31 / 5.29 kB; ScrollTrigger 42.75 / 17.55 kB; GSAP 69.94 / 27.41 kB; application CSS 198.21 / 32.59 kB; lazy 3D 856.53 / 227.75 kB. Final Full/High and Reduced/Fallback browser sessions reported zero console warnings or errors.
