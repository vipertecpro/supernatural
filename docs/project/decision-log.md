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
