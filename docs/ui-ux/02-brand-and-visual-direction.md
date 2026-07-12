# Brand and Visual Direction

## Working identity

**Project Codename: The Archive**. Naming, final logo, trademark clearance, and brand registration remain owner decisions.

Core attributes: mysterious, investigative, intelligent, communal, weathered, modern, and trustworthy. Avoid gore-led horror, ornate gothic pastiche, illegible darkness, generic gaming-dashboard neon, streaming-service mimicry, and recognizable television branding.

The original visual metaphor is a **night-road research archive**: a contemporary records room where routes, evidence, field notes, sources, and community observations converge. Public pages feel like entering the archive after dark; authenticated pages feel like working at a well-lit research desk inside it.

## Mark directions

Candidate families for owner exploration:

- intersecting route lines forming an abstract archive tab;
- layered document/compass geometry;
- a lantern aperture made from original polygons;
- a signal pulse crossing a keyhole-shaped negative space;
- a constellation of record nodes forming a unique monogram.

No pentagrams, anti-possession-like rings, Men of Letters-like geometry, official title lettering, Impala silhouette, or recognizable series props.

## Candidate colour architecture

Values are design candidates requiring contrast, display, high-contrast-mode, and colour-vision testing before implementation.

| Semantic role | Candidate | Use |
| --- | --- | --- |
| Ink / canvas | `#0B0D0F` | deepest public and dark-mode background |
| Carbon / surface | `#15191D` | primary dark surface |
| Ash / raised surface | `#252B31` | cards, drawers, dense workspace grouping |
| Bone / primary text | `#F4F0E6` | dark-mode primary text |
| Aged paper | `#D8CEB8` | editorial accents, not large low-contrast body copy |
| Cold moonlight | `#9DC7D8` | primary links/actions and focus candidate |
| Muted steel | `#78909C` | secondary metadata after contrast validation |
| Warning amber | `#E0A84A` | spoiler/warning state with icon and label |
| Evidence red | `#D45B5B` | destructive/restricted state with icon and text |
| Safe green | `#62A77A` | success/published state with icon and text |

Light mode uses warm bone/paper canvases with ink text; dark mode uses ink/carbon with bone text. Semantic tokens—not raw colours in components—cover `background`, `surface`, `surface-raised`, `foreground`, `muted`, `border`, `focus`, `action`, `danger`, `warning`, `success`, `spoiler`, `moderation`, and `editorial`. Colour never carries state alone.

## Typography

- **Interface:** existing Instrument Sans with `ui-sans-serif`, system-ui fallbacks.
- **Editorial reading:** `ui-serif`, Georgia, Cambria fallback initially; an openly licensed serif such as Source Serif 4 may be evaluated later without bundling now.
- **Display:** system/editorial serif or an approved openly licensed variable face such as Fraunces, used sparingly in public headings only.
- **Metadata:** `ui-monospace`, SFMono-Regular, Consolas, Liberation Mono for identifiers, timestamps, citations, and case references.

Body copy targets comfortable 65–75 character lines. Interface text does not use decorative all-caps paragraphs. Metadata may use modest tracking but remains selectable and readable.

## Materials

Grain, paper fibres, scratches, metal, glass, fog, dust, scan lines, and light leaks are procedural/original overlays. They remain low-opacity, never sit between text and its contrast surface, stop under reduced motion, and are excluded from dense workspaces. Shadows imply layered records rather than theatrical floating cards.

## Asset policy

Use abstract procedural graphics, original geometric symbols, rights-cleared contributor assets, and explicitly marked development placeholders. Every asset has provenance, license/ownership, alt/decorative intent, responsive variants, and removal path. No copyrighted screenshots, footage, quotes, soundtrack, actor likenesses, logos, or promotional art.

## Prompt 13 implementation note

The implemented working mark combines layered archive pages, a compass intersection, and a central record node. Warm-paper light and ink/carbon dark appearances use semantic OKLCH variables in `resources/css/app.css`. The codename, mark, and candidate colours remain subject to owner approval and formal contrast and brand review.
