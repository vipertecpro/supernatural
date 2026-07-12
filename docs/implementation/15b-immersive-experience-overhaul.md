# Prompt 15B immersive experience overhaul

## Scope

Prompt 15B adds a cinematic experience layer to the existing Prompt 15 public website without creating Catalog, Lore, Timeline, Media, Search, Community, Bunker, Messaging, Chat, Watch Room, Case Board, gamification, event, or NativePHP screens. All essential content remains semantic HTML.

## Runtime

`ExperienceProvider` is mounted once around the Inertia application. It resolves Automatic, Full, Balanced, Reduced, and Silent preferences into a visual mode and High, Medium, Low, or Fallback quality. Reduced motion and Save-Data always win. Coarse pointer, viewport, device memory, WebGL support, and prior WebGL failure can lower quality; none of these values are transmitted or used as a fingerprint.

Full enables lazy WebGL, GSAP choreography, Lenis, route transitions, and optional audio. Balanced lowers DPR/particles and uses lighter movement. Reduced prevents WebGL, smooth-scroll interception, the preloader, continuous motion, parallax, and audio. Silent uses Balanced visuals with sound disabled.

Preferences use local storage. The non-sensitive intro-viewed flag uses session storage. Sound begins muted and requires a user gesture.

## Dependencies

| Package | Version | Licence | Purpose |
| --- | ---: | --- | --- |
| `gsap` | 3.15.0 | GSAP Standard no-charge licence | ScrollTrigger, sequencing, parallax, masks, route/intro choreography |
| `@gsap/react` | 2.1.2 | GSAP Standard licence | React integration evaluated; runtime cleanup uses GSAP context directly |
| `lenis` | 1.3.25 | MIT | Eligible public-surface smooth scrolling |
| `three` | 0.180.0 | MIT | Procedural 3D environment |
| `@react-three/fiber` | 9.6.1 | MIT | React 19-compatible Canvas renderer |
| `@react-three/drei` | 10.7.7 | MIT | Approved helper dependency; no broad helper imports |
| `howler` | 2.2.4 | MIT | Reserved for future rights-cleared local audio loaded after opt-in |

`@react-three/postprocessing` was evaluated but not installed. Procedural lighting, fog, and CSS film treatment meet the visual need without its bundle/runtime cost.

## Fonts

Fontsource packages self-host Latin subsets for Instrument Sans 400/500/600, Cormorant Garamond 500/600, Cinzel Decorative 700, and Special Elite 400. Instrument Sans, Cormorant Garamond, and Cinzel Decorative are OFL-1.1; Special Elite is Apache-2.0. Body/controls use Instrument Sans, editorial headings use Cormorant Garamond, ceremonial display has Cinzel Decorative available, case-file labels use Special Elite, and evidence metadata retains system monospace. Fixed font metrics and explicit fallback stacks limit layout shift.

## Cinematic architecture

The first homepage visit in a browser session displays a real-progress entry sequence driven by document load and `document.fonts.ready`. It includes a keyboard-operable Skip button, never runs in Reduced mode, never mounts on auth redirects or other routes, and restores focus to the main landmark.

The hero lazy-loads a dedicated R3F chunk behind Suspense and an error boundary. It contains an original road, moving paired lights, fog, dust, floating abstract pages, a monolith/archive doorway, an original geometric ring seal, and restrained pointer camera response. It contains no vehicle, official prop, official symbol, recognizable location, or likeness. Canvas pauses offscreen and on hidden tabs, caps DPR by tier, avoids per-frame React state, and falls back to the complete existing CSS/SVG composition after unsupported WebGL, context loss, or chunk failure.

Homepage chapters keep their DOM reading order and gain GSAP entrance timelines and hero parallax. Lenis runs only on public surfaces, preserves native form/dialog scrolling, delegates anchors, synchronizes with ScrollTrigger, and destroys its RAF/ticker/listeners/triggers during mode or route changes.

Inertia links request the View Transition API where supported. A short archive-door fallback overlay follows Inertia start/finish/navigate events, releases on failure, and restores focus. Reduced mode disables the overlay. Workspaces retain fast functional movement without fog or WebGL.

## Sound

The audio controller uses Web Audio to synthesize an original low sine drone, filtered wind/static noise, and short interface tones. Nothing is fetched, sampled, or played before explicit activation. Ambient and effects volume are independent. Audio suspends on hidden tabs, resumes only if enabled, and stops in Silent/Reduced modes. Howler remains available only for future manifest-approved local audio.

## Provider media

TMDB is disabled unless a server token, numeric series ID, exact image host, terms acknowledgement, and commercial-use gate all pass. Laravel fetches/caches metadata server-side for six hours; the token is never a prop. Browsers receive responsive CDN metadata, attribution, and TMDB's non-endorsement notice. Images are never downloaded or proxied.

Official video uses existing public, moderated `ExternalEmbed` records. It additionally requires YouTube, `authorized_channel` provider metadata, and an effective embedding-rights review. The `youtube-nocookie.com` iframe is created only after a click. Without an approved record, an original radio-signal fallback is rendered.

## Security and fallbacks

Content Security Policy restricts images to self/data/blob/TMDB and frames to YouTube's privacy-enhanced host. Media, fonts, workers, forms, objects, and frame ancestors have explicit directives. Local-only HTTP/WS allowances support the Vite development server; production remains HTTPS/self allowlisted. Permissions Policy disables camera, microphone, geolocation, and payment.

No capability telemetry, analytics, user data, automatic third-party request, arbitrary iframe, arbitrary image host, user HTML, shader input, or Canvas fingerprinting was added.

## Performance budgets

Budgets are review gates: homepage page chunk under 40 kB raw, GSAP under 80 kB raw, ScrollTrigger under 50 kB raw, Lenis under 25 kB raw, lazy 3D under 250 kB gzip, public CSS under 36 kB gzip, individual fonts under 70 kB, no eager audio, and no eager iframe. The 3D chunk is intentionally over Vite's generic 500 kB raw warning but remains isolated and under its gzip budget.
