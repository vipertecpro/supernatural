# Prompt 5 Editorial Governance Implementation Audit

Date: 2026-07-12

## Result

The Catalog editorial-governance slice is implemented with additive schema, explicit revision and review actions, safe resources, version conflicts, normalized evidence/rights/spoiler records, minimal user spoiler context, audit records, after-commit events, factories, and focused tests. No dependency was added and no commit or push was performed.

## Migration evidence

- Migration: `2026_07_11_222550_add_catalog_editorial_governance.php`.
- Preview: passed with additive DDL and explicit legacy spoiler mapping.
- Isolated SQLite forward migration: passed.
- Isolated SQLite full rollback: passed after index-before-column rollback ordering was verified.
- Local execution: passed against `supernatural_db` on `127.0.0.1` as batch 5.
- Existing Catalog rows: zero; all new `lock_version` defaults are zero and no Catalog backfill was needed.
- Existing spoiler constraints: zero; no live classification row required mapping. The migration still preserves other installations through `mild → minor` and `critical → finale`.
- Destructive reset/refresh/wipe commands: not used.

## Integrity and security review

- Five revision target morph types and three revision-citation target morph types are allowlisted and backed by an enforced stable morph map.
- Revision field identifiers come only from a server registry. Actor, audit, lifecycle, publication, archive, permission, and arbitrary internal fields are not registered.
- Text blocks are plain text with checksums and conservative limits; no HTML, Markdown, transcript, or article-body support was added.
- Review assignment history is retained and a composite unique key prevents two active primary assignments.
- Author/reviewer separation is enforced in policy and action code. Moderators receive no implicit editorial authority.
- Approval rechecks code-owned source, rights, and spoiler requirements. Contributors cannot self-mark a citation verified, and spoiler approval requires the review permission.
- Unknown, prohibited, and expired rights never evaluate as allowed. Use types remain independent.
- Application locks revision and target rows, compares the target version and field checksums, revalidates relationships/evidence, increments once, and emits events only after commit.
- API conflicts use stable 409 codes. Private assignment, reviewer, and legal notes are omitted from normal Resources and audits.
- Public Catalog resources evaluate spoiler state before serialization; unsafe fields are null, hidden details return 404, and hidden list candidates are removed.
- Audit metadata was inspected for raw proposal/private text and tests prove sensitive proposal text is absent.

## Scope review

The implementation adds no lore, community, messaging, watch rooms, boards, gamification, events, notifications, media upload/processing, search engine, public/admin UI, NativePHP/mobile feature, external ingestion, Supernatural-specific fixture, or copyrighted asset/text. User Journey work is limited to the two tables needed for backend spoiler evaluation.

## Validation snapshot

- Focused Prompt 5 suite: 22 tests, 70 assertions.
- Full suite: 128 tests, 459 assertions.
- PHPStan level 7: 0 errors.
- Isolated migration rollback: passed.

Final validation passed: 128 Pest tests/459 assertions; focused 22/70; PHPStan with zero errors; Pint fix and test; ESLint fix/check; Prettier fix/check; TypeScript; Vite production build; strict Composer validation; Composer and npm audits; full and editorial route inspection; configuration and route caching followed by cache clearing; migration status; isolated rollback; and final Git whitespace/status review.

Intermediate verification exposed and resolved three implementation defects: SQLite rollback initially dropped indexed columns before their indexes, audited spoiler constraints initially lacked a morph alias, and the first static-analysis pass identified incomplete model type contracts. The corrected final reruns are green; no failure was suppressed.

## Remaining risks and decisions

- Legal review is still required for the exact business policy behind quotation, metadata reuse, expiry, takedown, and dual-control thresholds. The code is a policy enforcement mechanism, not legal advice.
- The minimal viewing-progress projection does not model multiple viewing orders, sessions, or rewatch selection; conservative results remain intentional until the User Journey phase.
- Database foreign keys cannot alone express cross-table universe/work/season/episode equality; the domain action enforces and tests those invariants transactionally.
- Public Catalog creation/publication remains a distinct existing action. This phase does not auto-publish approved changes and does not add publication scheduling.
- Notification, search, and moderation consumers for domain events remain deferred.
