# Frontend Application Boundaries

| Surface | Routes/layout | Data and loading | Real time / performance |
| --- | --- | --- | --- |
| Public website | proposed `/explore`, `/universes/*`, `/works/*`, `/lore/*`, `/timelines/*`, `/community`, `/bunkers`; `PublicMarketingLayout` / `PublicContentLayout` | SEO-complete safe public Resources, viewer-aware spoiler state, deferred noncritical related content with Skeleton/retry | cinematic modules dynamically imported only on selected pages; reduced-motion/no-WebGL/data-saver fallbacks; no Fan/workspace coupling |
| Fan application | proposed `/app/*`; `FanLayout` | verified owner/public/member data, effective capabilities, cursor lists, reversible optimistic interactions, explicit state profiles | no product realtime until explicitly implemented; fast navigation and modest functional motion |
| Contributor workspace | proposed `/workspace/contributor/*`; `WorkspaceLayout` | own/authorized revisions, evidence, rights, spoilers, Media; staged forms and conflict recovery | no cinematic or realtime dependency |
| Moderation workspace | proposed `/workspace/moderation/*`; `WorkspaceLayout` | case-scoped reports/evidence/actions/appeals; Journey and private safety lists excluded | calm queues, optional future scoped refresh only |
| Administration workspace | proposed `/workspace/administration/*`; `WorkspaceLayout` | capability-scoped Catalog/Lore/editorial/rights/Media/operations; dense tables and audit context only where APIs exist | predictable operation over decoration; missing operations stay visibly unavailable |

Shared components are low-level accessible UI primitives, state panels, cursor controls, spoiler warning, citation display, media rendering, and typed domain summaries. Public hero/WebGL/video, administration rights editors, and moderation evidence viewers are not shared across surfaces merely for visual reuse because their data, performance, and security contracts differ. Message composers do not exist until Messaging is implemented.

Wayfinder generates route/action functions; Page props and API Resources have explicit TypeScript interfaces co-located by domain. Inertia shared props stay small (identity, effective capabilities, flash, request ID). Large lists use deferred props with animated skeleton and rescue/retry; sensitive props are never deferred to unauthorized users. Standalone API calls use Inertia v3 `useHttp` or the project client, not unapproved Axios.

All surfaces require keyboard navigation, visible focus, semantic landmarks, contrast, screen-reader labels, reduced motion, caption/transcript handling for owned media, alt text, zoom/reflow, and non-color status cues. Authorization is server-side. Error handling preserves request ID and provides safe recovery without exposing internals.

Prompt 12 fixes the navigation and shell boundary in `docs/ui-ux/`: Public, Fan, Contributor, Moderation, and Administration are distinct contexts. Mobile Fan navigation is Home, Explore, Journey, Community, and More. Messaging, Watch Rooms, Case Boards, gamification, events, mobile, and other backend-deferred modules are not active navigation destinations.

Prompt 13 now enforces these boundaries in the layout and navigation layer. The workspace shell exists as a reusable configuration, but no operational workspace is mounted while the existing routes remain no-content stubs.

Prompt 14 adds a dedicated `OnboardingLayout` between Auth and Fan contexts. It receives only workflow metadata and authorized step props, uses generated Wayfinder actions, and persists no form value in browser storage. The Fan Dashboard is onboarding-gated; settings/security recovery stays outside that gate. Public Marketing remains unchanged.

Prompt 15 mounts the supported public Homepage/About/Open Source/Accessibility/policy set. Public cinematic code remains inside public page chunks/components and accepts no private Resource. The shared public layout adds a small local effects enum only; Fan/workspace state, permissions, mutations, and domain data are not imported. Prompt 16 owns every public knowledge-domain page.
## Prompt 15B boundary

The global provider contains only preferences, capability resolution, focus-safe route state, and opt-in audio. Three/GSAP/Lenis are lazy or public-surface-only. No private journey, notification, role, moderation, permission, draft, or telemetry data enters the experience runtime. Prompt 16 remains the owner of all knowledge-domain screens.
