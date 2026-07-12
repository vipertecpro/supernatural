# Prompt 15 Public Website Implementation Audit

## Scope and architecture

The implementation adds six supported public routes, five long-form Inertia pages, a complete homepage narrative, original SVG/CSS visual components, a local effects preference, safe metadata/structured data, reusable public navigation/footer, configuration, tests, and documentation. It adds no dependency, migration, database query, remote asset, copyrighted content, Canvas, WebGL, Three.js, React Three Fiber, GSAP, external video, sound, domain screen, workspace, Messaging, Watch Room, or mobile code.

## Security and privacy review

- Public page props contain only site name, registration availability, validated configured repository URL, fixed metadata, and fixed structured data plus the existing shared nullable auth identity.
- Journey, onboarding values, progress, notifications, drafts, moderation data, reporter details, roles, and permissions are absent.
- Canonical URLs require an explicit valid HTTPS `PUBLIC_SITE_URL`; local `APP_URL` is not silently published.
- Repository URLs require HTTPS, no URL credentials, and an allowlisted GitHub, GitLab, or Codeberg host. Missing or unsafe configuration hides the link.
- SVG is authored React markup without untrusted input or script. JSON-LD uses fixed server values and `<` escaping.
- External configured repository links use `target="_blank"` with `rel="noopener noreferrer"`.
- The effects preference stores only a non-sensitive enum. Runtime signals are neither persisted nor transmitted.
- No analytics, tracking, marketing form, iframe, open redirect input, remote resource, HTML injection, or Canvas fingerprinting exists.

## Accessibility review

Static review covers semantic landmarks, one primary heading, ordered chapter headings, skip navigation, skip introduction, keyboard-operated Radix menus/Sheet, focus treatment, current-page semantics, labelled effects state, readable static fallbacks, reduced-motion and Data Saver precedence, forced-colour visual removal, decorative SVG exclusion, touch controls, 320px-responsive CSS, and structured text alternatives to every decorative graph.

Manual screen-reader, forced-colour operating-system, long-translation, and full multi-browser assistive-technology verification remain required before any conformance claim.

## Automated evidence

- Focused public and shell suite: 25 tests, 292 assertions.
- Full Pest suite: 294 tests and 1,518 assertions passed.
- PHPStan: zero errors.
- Pint write/test modes, ESLint write/check, Prettier write/check, TypeScript, and the production build passed.
- Composer strict validation, Composer audit, and npm audit passed with no vulnerability advisory.
- Configuration and route caches built successfully; 312 routes loaded; generated caches were then cleared.
- `git diff --check` passed.

## Intermediate failures

- The first focused Pest run failed because the new route entry files had not yet been created and the existing Vite manifest could not resolve them. The pages were then implemented and the suite passed.
- The first post-style build rejected `@apply text-section-title` because that project component class is not a Tailwind utility. The rule now declares its type metrics directly and the build passes.
- The first TypeScript pass inferred the server snapshot as `string` in `useSyncExternalStore`; adding the explicit `EffectsPreference` generic corrected the contract.

## Bundle review

| Asset | Baseline | Prompt 15 | Change |
| --- | ---: | ---: | ---: |
| Main app JS | 207.39 kB / 61.06 kB gzip | 218.24 kB / 64.42 kB gzip | +10.85 kB / +3.36 kB gzip |
| Homepage JS | 4.41 kB / 1.97 kB gzip | 22.31 kB / 7.23 kB gzip | +17.90 kB / +5.26 kB gzip |
| CSS | 177.94 kB / 27.80 kB gzip | 192.53 kB / 31.02 kB gzip | +14.59 kB / +3.22 kB gzip |

Long-form page chunks range from 3.42 to 4.57 kB. The build transforms 2,370 modules and emits no warning. Hero effects use CSS only, activate while intersecting, and pause when the tab is hidden. No per-frame React state or runtime asset request exists.

## Visual review

The in-app browser exercised Homepage, About, Open Source, Accessibility, Content Policy, and Copyright/Takedown at 320×568, 390×844, 768×1024, 1024×768, and 1440×900. Every matrix entry rendered one `h1`, one main landmark, the public header/footer, correct current-page state, and no horizontal overflow. The exact 320px pass found and corrected the inherited `min-width: 320px` scrollbar overflow before the matrix was rechecked.

Dark/system desktop, 320px and 390px dark mobile, light mobile, and light desktop long-form policy views were visually inspected. Hero hierarchy, CTAs, wordmark, atmosphere, reading measure, chapter composition, and footer remained legible. Mobile Sheet navigation contained only supported routes, closed successfully, and restored focus to `Open navigation`. Automatic effects resolved conservatively; selecting Enhanced persisted the preference while the environment's reduction signal continued to win, as designed.

The first browser run exposed an existing `OfflineBanner` hydration mismatch when client network state differed from the server snapshot. `useNetworkStatus` now uses `useSyncExternalStore` with a stable online server snapshot. A fresh browser tab then produced no warning/error. Server-rendered markup fetched without JavaScript contains the entire semantic homepage, navigation, headings, CTAs, chapters, and footer.

VoiceOver/NVDA, forced-colour operating-system mode, true 200% browser zoom, long translated content, and non-Chromium rendering remain manual boundaries and are not represented as passed.

## Validation commands

Executed successfully: `php artisan test --compact`; `vendor/bin/phpstan analyse`; `vendor/bin/pint --format agent`; `vendor/bin/pint --test --format agent`; `npm run lint`; `npm run lint:check`; `npm run format`; `npm run format:check`; `npm run types:check`; `npm run build`; `composer validate --strict`; `composer audit --no-interaction`; `npm audit`; `php artisan route:list -v`; `php artisan config:cache`; `php artisan route:cache`; `php artisan optimize:clear`; `git diff --check`; `git status --short`; and `git diff --stat`. Wayfinder was regenerated with form variants after route changes.

## Remaining risks and owner decisions

The Archive remains a working codename. Final name, trademark, final mark, formal contrast measurement, full assistive-technology testing, and approved public reporting/contact channels remain owner decisions. Software licensing remains unresolved. Prompt 16 public domain screens and all later operational/community/messaging/mobile interfaces remain deferred.
