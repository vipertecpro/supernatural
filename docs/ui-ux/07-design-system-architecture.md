# Design System Architecture

## Existing foundation and target

The repository already has Tailwind CSS 4 CSS-first tokens, shadcn new-york/Radix configuration, Lucide icons, 57 UI primitives, Sonner, Recharts, TanStack Table, responsive sidebar/sheet primitives, theme switching, and `cn()`. Prompt 13 should evolve these assets rather than replace the component library.

The target system has four layers: **foundations → primitives → composites → domain components**. Immersive modules sit beside, never beneath, this stack.

## Foundation tokens

| Family | Proposed contract |
| --- | --- |
| Colour | semantic light/dark tokens from the candidate brand palette; no raw status colours in components |
| Type | display `clamp(2.5rem, 7vw, 6rem)` public-only; page title 2rem; section 1.5rem; body 1rem/1.6; compact UI .875rem/1.4; metadata .75rem |
| Spacing | 4px base: `1,2,3,4,6,8,10,12,16,20,24`; component internals favor `gap` |
| Radius | 4px compact, 8px controls, 12px cards, 16px feature panels; immersive masks may be square/irregular without affecting controls |
| Border | 1px semantic border; stronger divider for selected/operational states; never colour-only |
| Elevation | inset, surface, overlay, immersive; workspace tables primarily use borders, not card shadows |
| Z layers | base, sticky, dropdown, overlay, toast; overlay components own stacking |
| Motion | instant 0–80ms, functional 120–250ms, page 200–450ms, narrative variable; reduced motion 0–80ms fades only |
| Breakpoints | small phone 320–479; large phone 480–767; tablet 768–1023; laptop 1024–1439; desktop 1440–1919; large desktop 1920+ |
| Containers | reading 720px; content 1120px; wide 1440px; immersive full bleed with contained accessible content |
| Grid | 4 columns phone, 8 tablet, 12 desktop; 16–32px gutters |
| Icons | 16 compact, 20 default, 24 navigation, 32 empty state; accessible names belong to controls |
| Focus | 3px high-contrast semantic focus ring with offset; never removed |

All candidate colour pairs require contrast testing. High-contrast mode may replace texture/translucency with solid system colours.

## Component ownership

### Primitives

Reuse and theme existing Button, IconButton pattern, Link, Input, Textarea, Field, Checkbox, RadioGroup, Switch, Select, Label, Badge, Avatar, Tooltip, Separator, Spinner, Skeleton, Dialog, Sheet, Drawer, Table, Progress, Pagination, and Empty. Forms use `FieldGroup`/`Field`; overlays always have a title; Avatar always has fallback; status uses semantic Badge variants.

### Composites

`GlobalSearch`, `FilterBar`, `CursorControls`, `SpoilerBoundary`, `Citation`, `MediaPreview`, `ProgressControl`, `RatingControl`, `NotificationItem`, `UserSafetyMenu`, `StatusBadge`, `StatePanel`, `PermissionState`, `ConfirmAction`, `ConflictResolver`, and `OfflineBanner`.

### Domain components

Catalog: `UniverseCard`, `WorkCard`, `SeasonList`, `EpisodeRow`. Lore: `LoreEntityCard`, `RelationshipList/Graph`, `TimelineEntry`. Journey: `ContinueWatchingCard`, `JourneyProgress`, `WatchlistItem`. Community: `BunkerCard`, `BunkerRoleBadge`, `CommunityPost`, `CommentThread`, `ReactionGroup`, `Poll`. Trust/editorial: `ReportForm`, `RestrictionNotice`, `RevisionDiff`, `CitationPanel`, `RightsAssessment`, `CaseTimeline`.

### Immersive components

`CinematicHero`, `ArchiveScene`, `ScrollChapter`, `AmbientVideo`, `ProceduralAtmosphere`, `ObjectViewer`, `JourneyMap`, and an optional graphical relationship view. Every component accepts a static fallback and renders after essential content.

## State matrix

| State | Control | Card/list item | Domain record | Workspace row |
| --- | --- | --- | --- | --- |
| Default | labelled, enabled | identity + next action | authorized safe fields | primary cells + status |
| Hover/focus | non-spatial emphasis / visible ring | clear target | action affordance | row/action focus separate |
| Active/selected | pressed/current semantics | selected border + text | current tab/section | `aria-selected` where applicable |
| Disabled | reason adjacent when useful | not interactive | explain unavailable action | preserve readable value |
| Loading | spinner, stable label | shape-matched Skeleton | page/content Skeleton | table Skeleton, headers stable |
| Error | field or action message | retry affordance | StatePanel + request ID | row error does not collapse queue |
| Warning | icon + label + consequence | amber semantic variant | spoiler/conflict notice | attention badge |
| Restricted | generic or policy-safe reason | no protected preview | distinct restriction treatment | authorized reason only |
| Redacted | no sensitive DOM content | safe identity/placeholders | boundary and explicit reveal if allowed | not used to hide moderator-required data |
| Archived/removed | neutral status + allowed navigation | no primary mutation | history/policy-aware actions | immutable status/timeline |
| Empty | n/a | Empty component + next action | domain-specific explanation | clear filters/create action |

## Component rules

- Use existing shadcn components before custom markup; compose full Card structures and grouped menu/select items.
- Use Inertia `Link` and Wayfinder, semantic tokens, `gap`, `size-*`, `cn()`, and existing Radix `asChild` APIs.
- No immersive component owns auth, routing, form validation, permission checks, or essential data.
- Domain components accept already-authorized Resource shapes and never reconstruct hidden fields.
