# Prompt 15C Road Hero Baseline

## Repository state

- Branch: `main`
- Commit: `1b59ca11b23f911dc4b34ae24f3327856b386601`
- Prompt 15 and the initial experience runtime are present in the current commit.
- Prompt 15B-style immersive public-shell changes are present but uncommitted. They affect public layouts, shared experience scenes, CSS, and focused tests. They are treated as user-owned work and will not be reverted.
- `git diff --check` passed before Prompt 15C changes.
- Implementation can continue safely by adding a dedicated `road-hero` feature and limiting homepage integration to the opening hero.

## Existing opening experience

The homepage currently renders a CSS/SVG `ArchiveHeroScene` with a lazily loaded React Three Fiber scene. The tracked 3D scene is an abstract archive portal rather than a vehicle-following forest road. The page continues into the full Prompt 15 card and chapter narrative, which Prompt 15C permits temporarily hiding while the hero is rebuilt.

The current runtime already provides:

- React Three Fiber and Three.js Canvas rendering.
- GSAP and ScrollTrigger choreography.
- Lenis smooth scrolling with cleanup.
- Procedural Web Audio ambience and opt-in sound.
- Automatic, Full, Balanced, Reduced, and Silent experience choices.
- High, Medium, Low, and Fallback visual quality resolution.
- WebGL detection, context-loss reporting, reduced-motion and Save-Data handling.
- Instrument Sans, Cormorant Garamond, Cinzel Decorative, and Special Elite through self-hosted Fontsource packages.
- Public navigation for Home, About, and Open Source, plus the supported policy destinations in the footer.

## Installed cinematic dependencies

| Package | Version | Licence | Prompt 15C purpose | Current production cost before 15C | Alternative considered |
| --- | --- | --- | --- | ---: | --- |
| `three` | 0.180.0 | MIT | Renderer, geometry, materials, lights, fog | Included in the existing 3D chunk | Raw WebGL rejected for maintainability |
| `@react-three/fiber` | 9.6.1 | MIT | React scene graph and frame lifecycle | Included in the existing 3D chunk | Direct Three.js rejected for weaker React cleanup boundaries |
| `@react-three/drei` | 10.7.7 | MIT | Available loader/performance helpers | Included in the existing 3D chunk | Custom helpers only where smaller |
| `gsap` | 3.15.0 | GSAP Standard no-charge licence | ScrollTrigger camera and overlay choreography | 72 kB raw / 44 kB ScrollTrigger raw | Native scroll timelines rejected for inconsistent support |
| `@gsap/react` | 2.1.2 | GSAP Standard no-charge licence | React-safe GSAP context when useful | No separate measured entry chunk | Manual context cleanup remains acceptable |
| `lenis` | 1.3.25 | MIT | Desktop smooth scrolling synchronized with ScrollTrigger | 20 kB raw | Native scroll retained for reduced/coarse-pointer modes |
| `howler` | 2.2.4 | MIT | Installed but not required by this procedural soundtrack | No hero transfer while unused | Web Audio selected to avoid audio files and another runtime chunk |

## Existing production measurements

Measured from the production build present before Prompt 15C implementation:

| Asset | Raw | gzip |
| --- | ---: | ---: |
| Homepage chunk | 34,911 B | 11,409 B |
| Existing shared 3D chunk | 852,459 B | 223,443 B |
| Application CSS | 224,443 B | 37,522 B |

These are file-transfer measurements, not runtime timing claims. Prompt 15C will rebuild and measure again after implementation.

## Safety boundary

No episode, trailer, soundtrack, television audio, official logo, official symbol, game asset, ripped model, or unlicensed franchise media is present in the planned hero. No Prompt 16 page or second homepage section is in scope.
