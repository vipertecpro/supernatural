# Accessibility Standards

Target: design and implement toward WCAG 2.2 AA. This document is a requirement set, not a compliance claim.

## Global requirements

- Semantic `header`, `nav`, `main`, `aside`, and `footer` landmarks with one logical page `h1` and ordered headings.
- Skip links to main content and, on immersive public pages, skip intro/animation.
- Complete keyboard operation with visible focus, logical order, no traps outside managed dialogs, and no hover-only information.
- Dialog/Sheet/Drawer focus trap, accessible title/description, Escape behavior where safe, and focus restoration to the trigger.
- Persistent visible labels; instructions/errors programmatically associated; form summary links to invalid fields.
- Status updates use restrained polite/assertive live regions; no repeated announcements during background refresh.
- Normal text contrast 4.5:1, large text/UI graphics 3:1 where WCAG applies; semantic state always includes text/icon/shape.
- Reflow at 320 CSS px and zoom to 200% without loss of content/action; text resizing does not clip.
- Touch targets target 44×44 CSS px with adequate spacing.
- `prefers-reduced-motion` removes spatial/parallax/ambient motion; effects can also be disabled manually.
- High-contrast/forced-colours mode replaces texture, translucency, and colour-dependent borders with system-safe styling.

## Content and media

Informative images have concise contextual alt text; decorative grain/fog/symbols use empty alt or CSS. Original informational video has captions, transcript, visible controls, pause, and no autoplay sound. Audio is opt-in with transcript when meaningful.

Spoiler controls announce severity, withheld state, boundary, and reveal consequence before the control. Revealed content receives focus only when necessary and is announced without dumping long text into a live region.

## Complex components

- **Graphs:** accessible name/description plus searchable structured relationship table/list offering identical navigation and facts. Keyboard graph navigation is supplemental.
- **Timelines:** semantic ordered list with dates/sequence; spatial view is optional.
- **Tables:** caption, scoped headers, sortable state announcements, filter summary, row actions with record names, and mobile card alternative.
- **Polls:** `fieldset`/legend, selection rules, result values and percentages in text; charts are supplemental.
- **Reactions:** labelled toggle buttons with pressed state and accessible counts; no emoji-only meaning.
- **Drag/drop:** keyboard move controls or ordered inputs with position announcements.
- **Progress/rating:** native or ARIA-valued controls with current value, min/max, and non-pointer alternatives.
- **Diffs:** inserted/deleted labels in addition to colour; line/field navigation; plain-text alternative.

## Testing gates for later prompts

Each implemented prompt requires semantic/keyboard review, focus-path tests, zoom/reflow checks, reduced-motion checks, light/dark/high-contrast contrast checks, and screen-reader spot checks. Automated axe-style and component tooling may be proposed in Prompt 13 but must not be installed without approval. Public cinematic work additionally tests no-WebGL, failed-WebGL, data-saver, keyboard-only, and screen-reader reading order.

## Prompt 14 implementation note

Authentication/onboarding now includes one layout-owned main landmark, skip link, logical page `h1`, semantic ordered step navigation, `aria-current="step"`, polite current-step announcement, heading focus after navigation, focusable validation summary, visible labels, fieldset/legend groups, keyboard-native checkbox/radio cards, and mobile action placement. Formal screen-reader, forced-colour, 200% zoom, and multi-browser testing remains a manual gate; no WCAG conformance claim is made.
