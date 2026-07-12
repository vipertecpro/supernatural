# Project Decision Log

## 2026-07-12 — Prompt 1 repository audit boundary

### Status

Accepted for the audit baseline.

### Decisions

- Prompt 1 is limited strictly to repository auditing and documentation.
- No product functionality was intentionally implemented during Prompt 1.
- The evidence in the existing repository state determines the exact scope of Prompt 2.
- The platform is intended to be reusable across fandoms; shared application and data architecture must not hardcode Supernatural.
- Supernatural is the initial thematic implementation and configured fandom, not the platform's permanent domain boundary.
- Copyrighted media must not be committed, copied, rehosted, or distributed without appropriate rights. Original, licensed, embedded, externally referenced, or clearly marked demo-placeholder assets must be used according to their permissions.
- The immersive public website, logged-in fan dashboard, and administration/moderation dashboard have different UX, accessibility, security, and performance requirements and must be treated as separate experience surfaces.
- NativePHP Mobile 4 development will be handled in a separate repository only after this website/backend/dashboard repository and its API foundation are stable.

### Evidence and consequence

The audited repository is a fresh Laravel 13/Inertia React authentication starter with no fandom domain, administration/moderation authorization, community model, or stable mobile API. Prompt 2 should therefore stabilize the repository and establish a minimal reusable platform foundation before product features or thematic content are added. See `docs/audits/01-repository-audit.md` and `docs/audits/01-risk-register.md`.

## 2026-07-12 — Prompt 2 foundation stabilization

### Status

Implemented and verified within the foundation-only boundary.

### Decisions

- Authorization is first-party and database-backed. The initial fixed roles are fan, contributor, moderator and administrator; permissions remain the enforcement primitive.
- No administrator or other privileged login is seeded. Registration assigns only the fan role.
- Moderator permissions do not imply administrator access.
- Security-relevant authorization changes use a centralized audit logger. Audit metadata is recursively sanitized, excludes IP addresses, and stores a request identifier for correlation.
- Foundation tables use integer identifiers, matching the existing user schema and avoiding a mixed-key strategy before product scale requirements exist.
- Universes are reusable platform roots. Sources may optionally belong to a universe and carry a nullable rights record so unknown permissions are not misrepresented as approval.
- Spoiler constraints are polymorphic and introduced through a reusable model concern. Prompt 2 applies the concern to sources only; later content types may opt in deliberately.
- The public API contract begins at `/api/v1`, uses a consistent JSON envelope, request IDs, resources, safe exception mapping and distinct public/authenticated throttles.
- Reverb and Echo remain optional. Broadcasting is disabled unless explicitly configured, origins are allowlisted, client events are disabled, and private-user channels require authenticated verified ownership.
- The repository is source-available but has no approved software license. Composer metadata is not treated as an owner licensing decision; third-party reuse remains prohibited unless a root license is approved.
- Product features, content ingestion, community workflows, chat persistence, immersive UI and mobile implementation remain explicitly out of scope for this stabilization phase.

## 2026-07-12 — Prompt 3 product architecture

### Status

Accepted as the implementation blueprint; no product behavior implemented.

### Decisions

- Continue as a 17-module Laravel modular monolith. Universes are content roots, not SaaS tenants.
- Use a shared `works` catalog root with relational series/season/episode structure, translations, releases, collections and viewing orders.
- Use a typed `lore_entities` root with dedicated extension tables and real-foreign-key relationship edges; a graph database is deferred.
- Attach precise, multi-source citations to allowlisted records, fields, revision blocks, claims and graph assertions. Unknown rights deny hosting/derivation.
- Normalize spoiler boundaries to catalog identifiers and enforce visibility on the backend before serialization, search ranking, notifications or broadcast payloads.
- Store compact editorial revision patches plus text blocks and structured revision items; approvals atomically update the current record and audit the action.
- Separate hosted media assets from provider-authorized external embeds. No automatic third-party media download/rehosting.
- Begin search with a relational projection and introduce Scout/search infrastructure only at documented performance/quality thresholds.
- Persist accepted messages, membership, versions, receipts and room snapshots before Reverb broadcast; typing, presence and high-frequency sync remain ephemeral.
- Sequence implementation so Catalog, Editorial, Spoilers and Moderation precede community, messaging and watch rooms.

### Consequence

Prompt 4 is limited to the fandom-neutral Catalog Core described in `docs/architecture/16-implementation-sequence.md`. Prompt 2 remains uncommitted and its three migrations remain pending locally; those are explicit readiness gates, not architecture blockers.

## 2026-07-12 — Prompt 4 Catalog Core

### Status

Implemented and verified within the six-table Catalog boundary.

### Decisions

- Reuse the foundation `PublicationStatus` values `draft`, `published`, and `archived`; full submitted/review/approval states wait for the editorial revision phase.
- Follow Prompt 3's archive-first Catalog rule rather than adding `SoftDeletes`. Child-bearing roots cannot be hard-deleted and public visibility always checks ancestors.
- Use explicit Catalog permissions. Contributors own drafts, moderators receive no automatic editorial authority, and administrators receive publish/archive/delete capabilities.
- Normalize localized work locales and preserve the original title as the stable canonical title.
- Attach the existing spoiler concern through a stable morph map. Until progress/boundary tables exist, missing or non-safe classification conservatively removes summaries and synopses before serialization.
- Use restricted foreign keys, scoped unique indexes, and action-level cross-table work-type/parent checks. No destructive cascade or speculative table was added.

### Consequence

Prompt 5 can build editorial revisions, rights/source minimums, citations, review actions, and normalized spoiler boundaries against stable Catalog identifiers. It must not assume those deferred controls already exist.

## 2026-07-12 — Prompt 5 Catalog editorial governance

### Status

Implemented and verified within the Catalog editorial boundary.

### Decisions

- Use canonical Prompt 3 table names: `source_rights_reviews`, `revision_blocks`, `revision_items`, `review_assignments`, and immutable `editorial_actions`; API wording may call rights reviews assessments without duplicating the model.
- Keep proposal/review lifecycle separate from Catalog publication state. Approval does not publish; application writes approved changes transactionally.
- Use a code-owned target/field registry rather than client-controlled patch paths. Large text is plain text with checksums and conservative limits.
- Extend integer optimistic locking to all five supported Catalog revision targets and require expected versions at direct API mutation/lifecycle boundaries.
- Treat rights independently by use type; unknown, prohibited, or expired never permits a protected operation.
- Normalize spoiler boundaries to work/season/episode FKs, explicitly migrate legacy severity values, and enforce visible/warning/redacted/hidden decisions before serialization.
- Implement only per-universe tolerance and per-work highest progress from User Journey. Viewing sessions, rewatch selection, lists, ratings, notes, and feeds remain deferred.
- Dispatch submitted, approved, and applied events only after commit and never through Reverb in this phase.

### Consequence

Catalog changes now have attributable proposal, evidence, review, concurrency, and spoiler enforcement foundations suitable for reuse by the next approved domain slice. Legal policy, takedown integration, full progress/order semantics, event consumers, and UI remain future decisions.

## 2026-07-12 — Prompt 6 Lore and Knowledge Graph

### Status

Implemented and verified within the relational Lore boundary.

### Decisions

- Implement the Prompt 3 typed root, seven approved extensions, translations, aliases, taxonomies, appearances, controlled relationship types/rules, assertions, named timelines, ordered entries, and entity-entry associations.
- Correct the inventory omission by adding `timelines`; keep `lore_event_details` as the canonical event extension name.
- Store symmetric relationships once in lower-ID order and use inverse labels only for presentation.
- Reuse citations, tri-state source-rights history, editorial revisions, normalized spoilers, optimistic locking, audit logging, permissions, and the v1 envelope; create no parallel evidence or spoiler framework.
- Keep public graph reads one hop and cursor-bounded to 50. Do not implement recursive traversal, search/media projections, UI, or Prompt 7.

### Consequence

The backend now has a governed multi-universe Lore source of truth. Prompt 7 may consume published Lore events or IDs but must preserve the established module boundary and deferred search/media decisions.

## 2026-07-12 — Prompt 7 Media and relational Search/Discovery

### Status

Implemented and verified within the Phase 4 boundary.

### Decisions

- Implement the exact five Media and four Search inventory tables; Catalog/Lore remain authoritative and every Search row remains rebuildable.
- Admit actual uploads only to the private configured quarantine disk with server keys, MIME/extension/size/image validation, checksums, and private-by-default state. Transformation, signed delivery, malware integration, and cloud-provider selection remain deferred.
- Keep hosted assets and external embeds separate. Allow only configured YouTube, Vimeo, Spotify, and SoundCloud HTTPS URLs, generate trusted embed URLs locally, and never fetch/download remote content.
- Reuse Source tri-state rights independently: licensed hosting requires `hosting=allowed`; embeds require `embedding=allowed`; unknown/prohibited/expired deny.
- Restrict attachments to stable Catalog/Lore morph aliases, one media source, same-universe targets, deterministic ordering, public compatible lifecycle, and backend spoiler filtering.
- Project published universe/franchise/work/season/episode/Lore entity/timeline sources into locale-specific relational documents after commit. Source events are scalar-ID, idempotent, non-broadcast, and synchronous while work remains bounded.
- Use portable normalized token/title ranking with safe alias/summaries and source-resolved spoiler filtering before pagination. Do not claim typo tolerance or semantic search.
- Store Search interaction input only as HMAC query hash, length, public filter/locale, coarse result bucket, and time. Trending publication waits for approved retention/abuse/minimum-sample operations.

### Consequence

Prompt 8 should implement only the next User Journey phase, expand viewing sessions/orders/preferences as approved, and reuse Search/Spoiler contracts. Community, moderation, notifications, messaging, and external search remain later phases.

## 2026-07-12 — Prompt 8 User Journey

### Status

Implemented and verified within the Phase 5 boundary.

### Decisions

- Keep viewing orders/items Catalog-owned and correct the 12-table User Journey inventory by replacing three deferred reservations with journeys, progress events, and rewatch cycles.
- Expand existing progress/preferences in place; preserve legacy positional spoiler semantics and append-only knowledge history.
- Make all personal journey data owner-private; reserve visibility values without exposing a public projection.
- Use integer basis points/seconds, scoped idempotency keys, row locks, expected versions, and null-safe active/default/scope keys.
- Keep Search projections/ranking global and join authenticated progress only at query time.
- Delete identifiable current/historical journey data with account deletion; defer the export endpoint/UI while documenting a versionable structure.

### Consequence

Prompt 9 should implement only the minimum moderation and stable-notification phase. It may consume scalar after-commit journey events but must not expose private viewing history, introduce Community early, or broadcast playback/progress through Reverb.

## 2026-07-12 — Prompt 9 Moderation and stable Notifications

### Status

Implemented and verified within the Phase 6 boundary.

### Decisions

- Keep Identity-owned user restrictions normalized as restriction plus capability scopes; evaluate active/lifted/expired rows centrally and do not exempt administrators implicitly.
- Implement the nine currently required Moderation workflow tables. Keep copyright-notice and trust-signal reservations deferred because no public legal intake or automated trust system is approved.
- Use the canonical Notification tables for inbox, preferences, and deliveries. Keep stable type definitions code-controlled; defer digest and push tables.
- Make moderation actions and appeal decisions append-only; use new attributable records for correction, modification, and replacement.
- Hide active restricted content through existing public scopes and Search projection events without changing source publication, rights, Media lifecycle, or spoiler state.
- Render notifications for the recipient from scalar payloads and trusted route keys. Queue delivery after commit and never use Reverb for personal/moderation activity.
- Consume Journey and rewatch completion only; do not consume progress/session/playback/private-note activity.

### Consequence

Prompt 10 may begin only the approved Community phase after this uncommitted change is reviewed. Community must use the report/restriction interfaces, retain backend spoiler/media gates, and must not begin Messaging or public activity-history exposure.
