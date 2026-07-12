# Prompt 15 Public Website Baseline

Captured before Prompt 15 implementation on 2026-07-13.

## Repository evidence

- Branch: `main`.
- Commit: `0d4cf695cd3c13778759145f3ea0ad17a67c73c4` (`Implement onboarding workflow persistence and conflict handling`).
- Prompt 14 is committed at HEAD. The handoff statement that Prompt 14 was uncommitted is stale; the Prompt 14 commit follows the Prompt 13 baseline commit `30c06b423b1beea0d8939a1b431565353b9a1b23`.
- Initial worktree: clean. `git status --short` returned no entries and `git diff --check` passed.
- Runtime: PHP 8.4, Laravel 13.19.0, Inertia Laravel 3.1.1, React adapter 3.6.1, React 19.2.7, Tailwind CSS 4.3.2, Wayfinder 0.1.20, and Pest 4.7.5.
- `php artisan route:list -v` reported 307 routes before implementation. No migration is required or authorized for Prompt 15.

## Existing public surface

- `/` was the only public marketing route and rendered `resources/js/pages/welcome.tsx` through `PublicMarketingLayout`.
- The welcome page was a Prompt 13 design-foundation preview with a static brand mark, one principle grid, registration/sign-in actions, and no product narrative.
- Public navigation contained only Home plus guest authentication or authenticated `Open app`; the mobile Sheet matched that limited set.
- `PublicContentLayout`, the original archive/compass `BrandMark`, `BrandWordmark`, appearance preference, semantic motion tokens, reduced-motion CSS, skip link, and basic footer already existed.
- No About, Open Source, Accessibility, Content Policy, or Copyright and Takedown web route/page existed.
- No public metadata helper, canonical URL contract, structured data, social-preview asset, repository URL configuration, effects preference, Canvas effect, or public visual component library existed.
- No motion, WebGL, Three.js, React Three Fiber, or GSAP dependency was installed. The implementation can safely continue by composing existing React, CSS, SVG, Radix, Lucide, and native browser APIs.

## Configuration and bundle baseline

- `APP_URL` resolved locally to `http://supernatural.test`, but no intentionally configured public canonical URL existed.
- No repository URL configuration existed. The root repository remains source-available without an approved standalone software license.
- Pre-change production build passed with 2,355 transformed modules.
- Main application chunk: 207.39 kB, 61.06 kB gzip.
- Existing welcome page chunk: 4.41 kB, 1.97 kB gzip.
- CSS: 177.94 kB, 27.80 kB gzip.
- Largest shared dependency chunk: 348.42 kB, 109.64 kB gzip.
- Build emitted no warning.

## Safety decision

The baseline is clean, Prompt 14 is committed, the public work is route- and component-isolated, and the requested phase requires no database work. Implementation can continue while preserving the authenticated/onboarding and API contracts and keeping Prompt 16 domain routes absent.
