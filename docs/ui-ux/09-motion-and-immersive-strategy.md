# Motion and Immersive Strategy

## Motion language

Motion behaves like records being revealed, aligned, connected, or filed—not like a horror jump scare.

| Category | Uses | Duration | Constraints |
| --- | --- | --- | --- |
| Functional | dialog/sheet, tabs, filter expansion, progress confirmation, loading | 120–250ms | never delays completion; focus moves immediately |
| Page/context | Inertia navigation, detail panel, workspace switch | 200–450ms | modest fade/translate; preserve scroll/focus rules |
| Narrative | homepage chapters, public universe context, timeline/relationship exploration | sequence-specific | no scroll hijack; skip/pause; static reading order |
| Ambient | procedural fog, grain, dust, light/parallax | slow/continuous | off for reduced motion/data saver; user toggle |

CSS handles focus, hover, simple disclosure, and base transitions. A future approved motion library may coordinate component/page transitions. GSAP-like sequencing is conceptually suitable only for isolated public narratives. React Three Fiber/Three.js-like tooling is conceptually suitable only for lazy immersive modules. No dependency is selected or installed here.

## Appropriate immersive candidates

- Homepage opening archive/night-road scene with static poster fallback.
- Original archive object or symbolic mark viewer.
- Public relationship visualization paired with an HTML list.
- Timeline/journey map enhancement paired with a chronological list.
- Special feature page using rights-cleared original assets.

Authentication, settings, forms, watchlists, notifications, Community composer, moderator queues, and administration never require WebGL or narrative motion.

## Performance tiers and fallback

| Tier | Selection | Experience |
| --- | --- | --- |
| A Full | capable device, no reduced motion/data saver, user effects enabled | lazy WebGL, optimized owned video, restrained atmosphere |
| B Enhanced | average capability or WebGL unavailable | CSS/motion, optimized poster/video, no 3D requirement |
| C Essential | reduced motion/data saver/low capability/render failure/user choice | static responsive art, minimal fades, full content/function |

The first render is always Tier C-capable. Enhancement begins after meaningful content and consent/capability checks. Failure is silent except an optional effects setting; it never produces an error page.

## Planning budgets

These are review gates, not performance guarantees:

- Shell route JavaScript: target ≤200KB compressed initial application code, measured after Prompt 13.
- Immersive code: separate lazy chunk; target ≤300KB compressed before 3D engine/model payloads.
- Public hero poster: ≤250KB modern format per selected viewport.
- Initial optional video: poster first; ≤4MB short muted loop, multiple encodes, never required.
- Initial 3D model: ≤1.5MB compressed; textures ≤2MB total for first scene; load after interaction/idle.
- Content images: responsive `srcset`, explicit dimensions, lazy below fold; per-image budgets chosen by role.

## Safety rules

No autoplay sound, cursor replacement, aggressive flicker, essential timed content, or endless cinematic loader. Ambient control is keyboard accessible, remembers a local preference, and does not imply account-level sync unless an API is later added. Original video receives captions/transcript when it communicates information.
