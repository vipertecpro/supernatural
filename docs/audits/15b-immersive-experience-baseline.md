# Prompt 15B immersive experience baseline

Date: 2026-07-13

## Repository state

- Branch: `main`
- Baseline commit: `0d4cf695cd3c13778759145f3ea0ad17a67c73c4`
- Baseline subject: `Implement onboarding workflow persistence and conflict handling`
- Prompt 15 is not committed. Its public website implementation is present as a dirty working tree and is the additive foundation for Prompt 15B.
- `git diff --check` passed before Prompt 15B changes.
- Laravel 13.19.0 runs on PHP 8.4.23 with MySQL, Reverb, database queues/sessions/cache, and FrankenPHP Octane.
- Herd resolves the homepage to `http://supernatural.test`; no development server was started.

The initial worktree contained Prompt 15 modifications to `.env.example`, `README.md`, architecture/audit/project/UI documentation, `resources/css/app.css`, the Inertia bootstrap, public layouts/pages/components/content/types, `routes/web.php`, `config/public-site.php`, `PublicPageController`, and `PublicWebsiteTest`. These changes are user-owned and must remain intact.

## Prompt 15 verification

- Full baseline suite: 294 tests, 1,518 assertions, all passing.
- Baseline production build: successful with Vite 8.1.4.
- Baseline homepage page chunk: 22.31 kB raw / 7.23 kB gzip.
- Baseline application entry chunk: 218.24 kB raw / 64.42 kB gzip.
- Baseline JSX runtime chunk: 316.96 kB raw / 99.65 kB gzip.
- Baseline application CSS: 192.53 kB raw / 31.02 kB gzip.
- Instrument Sans baseline output: three weights, with WOFF/WOFF2 subsets emitted by the Laravel Vite font plugin.

## Existing public experience

Prompt 15 provides the homepage plus About, Open Source, Accessibility, Content Policy, and Copyright and Takedown pages. Public routes use `PublicMarketingLayout` or `PublicContentLayout`, typed Wayfinder routes, semantic DOM content, skip links, responsive layouts, metadata/structured data, and a mobile sheet navigation.

The homepage currently uses original CSS/SVG atmosphere: a road, signal line, document layers, evidence graph, journey path, bunker network, spoiler states, and a source ledger. Intersection Observer toggles restrained CSS animation. There is no GSAP, Lenis, Three.js, React Three Fiber, WebGL, video, image provider, audio, cinematic preloader, or global route-transition runtime.

The existing `usePublicEffects` preference supports `automatic`, `enhanced`, and `reduced`. It considers reduced motion, Save-Data, coarse pointer, mobile viewport, device memory, and document visibility without transmitting capability data. Prompt 15B must replace this narrow public preference with the requested Full, Balanced, Reduced, and Silent runtime while preserving compatibility and accessibility.

## Media and rights baseline

The Media domain already contains rights-aware `MediaAsset` and `ExternalEmbed` models, source and content-licence relationships, independent hosting and embedding decisions, public visibility scopes, moderation/processing states, takedown fields, and provider URL normalization. YouTube normalizes to `www.youtube-nocookie.com`; Vimeo, Spotify, and SoundCloud are also supported by the general domain allowlist.

The database contains `media_assets`, `media_variants`, `media_attachments`, and `media_processing_jobs`, including attribution, copyright owner, source, content licence, visibility, moderation, processing, and takedown data. `MediaRightsService` treats project-original/user-owned assets separately and requires an effective rights review for licensed hosting or embedding.

There is no Prompt 15B experience asset manifest, TMDB configuration/provider, public approved-embed presentation, experience-specific manifest validator, or public attribution payload. No arbitrary remote media exists in the Prompt 15 implementation.

## Security and delivery baseline

- No application Content Security Policy implementation was found in application, bootstrap, configuration, route, resource, or test files.
- Public metadata accepts only clean HTTPS canonical origins and allowlisted HTTPS repository hosts.
- Public props exclude private journey, notification, moderation, permission, role, and draft data.
- The existing Vite/Inertia setup already code-splits page components, but cinematic dependencies and a dedicated 3D chunk do not exist.
- No sound preference or audio runtime exists.
- No performance monitor or explicit experience asset budgets exist.
- No route-transition behavior exists beyond Inertia's progress bar and component replacement.

## Fonts

Instrument Sans 400/500/600 is configured through the Laravel Vite font plugin and emitted locally during builds. Editorial and evidence typography currently rely on system serif and monospace fallbacks. No Supernatural title font or copied franchise typography is present.

## Safe-continuation decision

Implementation can continue additively. Prompt 15 is verified and its public boundary is explicit. Prompt 15B must preserve all dirty work, keep Prompt 16 routes/screens absent, use only original procedural visuals until rights-cleared provider data is configured, keep sound opt-in, and retain a complete DOM/CSS fallback.
