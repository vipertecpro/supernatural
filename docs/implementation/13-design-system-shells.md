# Prompt 13 Design System and Application Shells

## Implemented scope

Prompt 13 replaces Laravel starter identity with the original working codename **The Archive** and establishes a reusable frontend foundation. It implements no domain screen, domain request, route, migration, WebGL, Messaging, realtime product delivery, or mobile application code.

## Structure

- `components/brand`: original monochrome archive/compass SVG mark and wordmark.
- `components/shell`: page container, header, section, and responsive content grid.
- `components/navigation`: appearance menu, mobile bottom navigation, empty-safe workspace switcher.
- `components/states`: loading, empty, request error, restriction, conflict, unavailable, and offline states.
- `components/spoiler`: safe/minor/moderate/major/finale labels and visible/warning/redacted/hidden rendering contracts.
- `components/status`: lifecycle, visibility, and authority badges, including separate platform and Bunker moderator labels.
- `layouts/public`: marketing and future content-reading shells.
- `layouts/fan`: desktop/tablet sidebar and mobile bottom-navigation shell.
- `layouts/workspace`: shared contributor, moderator, and administrator configuration surface.
- `lib/shell/navigation.ts`: centralized route-safe navigation using generated Wayfinder helpers.

## Foundations

The Tailwind CSS 4 CSS-first theme defines warm light and atmospheric dark appearances, backgrounds, surfaces, foregrounds, borders, actions, success/warning/danger/information, editorial/moderation/restricted/archived/offline, four spoiler severities, chart placeholders, graph/timeline/evidence placeholders, serif display, sans interface, monospace evidence, radius, elevation, shell dimensions, content widths, safe-area behavior, and four motion durations.

The brand mark combines layered archive pages, a compass intersection, and a central record node. It is original, monochrome, SVG-only, supports decorative and meaningful use, and contains no recognizable television mark or copyrighted asset.

## Shell behavior

- Public Marketing: skip link, sticky responsive header, keyboard-managed mobile Sheet, appearance control, only existing Home/auth/app routes, hero slot, main landmark, and footer.
- Public Content: readable article width, optional sticky context, source, related-content slots, and wide mode. It remains unused until a future approved domain page.
- Fan: persistent desktop sidebar, tablet collapse, compact header, breadcrumbs, appearance, user menu, offline state, safe-area mobile bottom navigation, and content clearance.
- Workspace: one shared component with contributor, moderator, and administrator density/tone configuration. Existing `/moderation` and `/administration` routes are no-content stubs, so no shell is falsely attached and no workspace link is rendered.
- Auth and settings: the existing Fortify/Inertia forms retain their actions and validation while adopting the brand, semantic surfaces, skip link, responsive framing, and appearance control.

## Navigation and authorization

Navigation uses generated Wayfinder helpers. Only Home, Dashboard, Profile/Settings, login, and registration are rendered because those page routes exist. Workspace destinations arrive as a server-filtered shared prop; the current list is deliberately empty until real Inertia workspace routes exist. Hiding navigation is not treated as authorization, and the existing middleware/policies remain authoritative.

## Accessibility and privacy

Implemented: semantic landmarks, skip links, one page `h1` on updated pages, visible three-pixel focus treatment, Radix Sheet/Dropdown focus management and restoration, current-page labels, 44px-class mobile targets, safe-area bottom spacing, reduced-motion overrides, forced-colours texture removal, labelled live offline/loading/error states, non-colour status icons/text, and 320px minimum reflow intent.

No claim of WCAG conformance is made. Manual screen-reader, browser zoom, operating-system high-contrast, long-translation, and multi-browser checks remain required.

## State and threat contracts

Spoiler redaction never receives hidden body text. Offline handling detects connectivity and never silently queues mutations. Restricted and generic unavailable states avoid permission keys, reporter identity, block direction, private Bunker existence, Journey data, and internal moderation detail. No sensitive Inertia Resource is persisted to local storage.

## Deferred work

Domain Catalog/Lore/Search/Journey/Community/Bunker pages, onboarding, contributor workflows, moderator queues, administration CRUD, Messaging, Watch Rooms, native mobile, cinematic media, and immersive rendering remain deferred to their approved prompts.
