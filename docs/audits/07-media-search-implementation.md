# Prompt 7 Media and Search Implementation Audit

Date: 2026-07-12

## Result

The approved Phase 4 slice is implemented: five rights-aware Media tables, four relational Search tables, private upload quarantine, four-provider external embeds, allowlisted Catalog/Lore attachments, rights/moderation/version actions, rebuildable multi-locale projections, after-commit consumers, manual reconciliation, safe relational search/suggestions/related discovery, permissions/policies, 17 API routes, audit events, factories, tests, and operational documentation. No dependency, copyrighted/fandom-specific content, remote fetch/download, commit, or push was added.

## Baseline and migration evidence

- Initial branch/commit: `main` at `d176086962c588cb88280bce314555fd943834b9`.
- Prompt 5 and Prompt 6: committed together at that commit; initial worktree clean.
- Initial pending migrations: none. Database: local MySQL `127.0.0.1`; queue: database; private/public/S3 disk definitions present.
- Created migration: `2026_07_12_120000_create_media_search_tables.php`; no backfill and no existing row mutation.
- SQL preview: additive only.
- Isolated SQLite: complete forward migration passed; `search:rebuild` dry-run, normal, repeated, and explicit-prune modes passed; complete rollback passed and returned every migration to pending.
- Initial local MySQL execution exposed one overlong auto-generated index name. Because MySQL DDL is non-transactional, eight partial Prompt 7 tables remained. `information_schema` verified all eight had zero rows; only those empty new tables were removed. The index was explicitly shortened and the migration then passed as batch 7.
- Local final state: all migrations applied; nine Prompt 7 tables present; all Media/Search rows remain zero; 12 new permissions seeded idempotently.
- Data-loss result: no existing row, Catalog/Lore/rights/spoiler record, storage object, or user data was changed or deleted. The recovery removed only verified-empty untracked Prompt 7 schema from the failed first attempt.

## Implemented components

- Models/factories (9 each): MediaAsset, MediaVariant, ExternalEmbed, MediaAttachment, MediaProcessingJob, SearchDocument, SearchSuggestion, TrendingSnapshot, SearchQuery.
- Enums (12): Media kind/origin/status/moderation/processing/visibility/provider/attachment role/variant purpose and Search document/projection/suggestion types.
- Media: upload, embed, update, lifecycle, attachment actions; rights and provider services; three policies; safe API Resources.
- Search: deterministic projector, query/suggestion service, related-content service, after-commit listener, and `search:rebuild` command.
- Domain events added: `MediaPublished`, `MediaArchived`, `SearchProjectionRequested`, `SearchProjectionRemovalRequested`.
- Events consumed: new projection request/removal plus existing `LoreEntityPublished`, `TimelinePublished`, and `EditorialRevisionApplied`. Catalog/Lore publication/archive actions now request refresh/removal after commit.
- Audit events: `media.asset_created`, `media.external_embed_created`, `media.updated`, `media.attached`, `media.detached`, `media.attachment_published`, `media.published`, `media.archived`, and `search.projection_rebuild_completed`.

## Behavior summary

- Hosted media: actual admission to private quarantine with server key, MIME/extension/size/image verification, checksum, dimensions, sanitized display filename, and private/pending defaults. Resources expose no disk, storage key, private original path, checksum, or provider/legal metadata.
- Binary delivery: deferred. No signed URL, public file route, image processing package, malware provider, transcoder, or cloud account is required.
- Embeds: YouTube, Vimeo, Spotify, and SoundCloud HTTPS host allowlists; canonical ID/URL and trusted embed URL generated locally; arbitrary HTML/script and unsupported domains rejected; no network request.
- Rights: licensed hosted assets require current effective `hosting=allowed`; embeds require `embedding=allowed`; project-original/user-owned origins retain explicit basis; permissions never imply one another; expiry/unknown/prohibited/takedown deny.
- Moderation: pending/approved/restricted/rejected/takedown-pending/removed enum; publication requires approved state. Only explicit permissions may moderate/publish/archive.
- Attachments: exactly one source in application validation, 13 target morph aliases, same-universe validation, duplicate/primary/order/locale constraints, draft/public compatibility, and conservative spoiler filtering.
- Variants/processing: schema/model lifecycle only; public Resources expose only ready variants. No generation worker starts.
- Editorial: existing rights, citations, audit, and optimistic-lock foundations are reused. A parallel Media review workflow was not created; full Media revision-field registration remains deferred.
- Search: Catalog/Lore source rows remain authoritative. Public universe/franchise/work/season/episode/Lore entity/timeline projections are deterministic, locale-specific, safe-text bounded, source-versioned, and removable.
- Ranking: exact canonical, exact localized, canonical prefix, localized prefix, title token match, controlled weight, stable ID; at most 250 candidates. No typo or semantic claim.
- Spoilers: unsafe summaries/sensitive aliases are not projected; authoritative sources are re-evaluated before result/suggestion pagination; hidden rows/counts disappear and redacted excerpts are null.
- Suggestions: prefix length 2-100, maximum 10, safe titles/slugs/aliases only.
- Related: same-franchise Work and shared-taxonomy Lore only; self/publication/spoiler filters, maximum 20, deterministic explanation keys.
- Interactions/trending: Search stores HMAC query hash, length, locale/public filters, result bucket, and timestamp only. Trending schema exists; aggregation/public endpoint waits for retention, abuse, and minimum-sample approval.

## Authorization and API

Contributors receive Media draft create/update/attach only. Moderators receive no Media rights authority. Administrators receive all Media/Search permissions. Public reads use `api-v1-public`; all 11 Media writes show `auth:sanctum`, `verified`, and `throttle:api-v1`. Search administration has no HTTP endpoint.

Added routes: 14 Media, two Search, one Discovery. Total route inventory is 164.

## Tests and validation

Added 13 focused Pest tests / 68 assertions covering upload safety/path secrecy, MIME confusion, provider/HTML rejection, independent rights, Media lifecycle, attachment target/universe/duplicate rules, write authorization/verification/conflicts, multi-locale projection, draft/archive removal, idempotency/version refresh, exact ranking, raw-query privacy, sensitive aliases, all rebuild modes, guest/optional-Sanctum spoiler context, and API validation/private-field omission.

Final passing evidence:

```text
vendor/bin/pint --dirty --format agent
vendor/bin/pint --test --format agent
php artisan test --compact                         164 tests, 640 assertions
php artisan test --compact tests/Feature/Media tests/Feature/Search
                                                     13 tests, 68 assertions
vendor/bin/phpstan analyse --no-progress           0 errors
npm run lint
npm run format
npm run format:check
npm run types:check
npm run build
composer validate --strict
composer audit --no-interaction                    no advisories
npm audit                                           0 vulnerabilities
php artisan route:list -v                          164 routes
php artisan config:cache
php artisan route:cache
php artisan optimize:clear
php artisan migrate:status                         all applied
git diff --check
```

Projection command evidence includes direct isolated dry-run/normal/repeated/prune execution and focused command tests with a published source and stale projection.

Failed commands were not suppressed:

1. First local MySQL migration failed on identifier `trending_snapshots_universe_id_subject_type_window_ended_at_index` exceeding 64 characters. Fixed with explicit `trending_universe_type_window_index`; verified-empty partial new tables were narrowly removed; final migration passed.
2. The first partial-table cleanup Tinker expression was over-escaped and parsed unsuccessfully; it made no change. The corrected `Schema` alias expression succeeded before the final migration.
3. Initial focused Pest run had one MIME-confusion test failure because fake image MIME alone was insufficient; server image-structure validation was added and the final focused/full suites passed.
4. Initial PHPStan run reported new model/dynamic-builder contracts; explicit enum/model properties, generic relations, typed branches, and list shapes resolved all errors without suppression.

## Files and dependencies

- Created: 74 files after this audit (domain/actions/services/events/listener/command, optional Sanctum resolver middleware, 12 enums, 9 models, 3 policies, 8 requests, 4 controllers, 4 resources, config, 9 factories, one migration, two test files, and three Prompt 7 documents).
- Modified: 22 existing files across source lifecycle events, permissions/provider/bootstrap/routes, architecture/ADRs/project/policy/risk/readiness documentation.
- Dependencies added: none.
- Architecture deviations: none. The only correction was an explicit MySQL-safe index name; table names/counts and accepted ADR semantics remain unchanged.

## Remaining risk and Prompt 8 readiness

Media risks: legal/takedown policy, malware scanning, storage/DB orphan reconciliation, decompression/resource limits, signed delivery, transformation security, and dual rights review. Search risks: relational relevance/latency at scale, projection lag, operational reconciliation, query retention, trending k-anonymity/abuse controls, and richer progress/order spoiler context.

The repository is technically ready for Prompt 8 after owner review of this uncommitted diff. Prompt 8 should implement only the approved User Journey phase: expand viewing sessions/progress/orders/preferences and their privacy/conflict semantics, reuse the existing spoiler evaluator and Search result contract, and avoid Community, moderation, notifications, messaging, mobile, or external Search work.

No media was downloaded or rehosted. No copyrighted or Supernatural-specific content was added. Nothing was committed or pushed.
