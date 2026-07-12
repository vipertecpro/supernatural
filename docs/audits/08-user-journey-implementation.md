# Prompt 8 User Journey Implementation Audit

Date: 2026-07-12

## Result

The approved Phase 5 slice is implemented with two Catalog viewing-order tables, ten new User Journey tables, two safely expanded existing tables, owner-private API v1 resources, lifecycle/progress/session/rewatch actions, continue watching, personal library/preferences, canonical spoiler evaluation, query-time Search personalization, policies, four viewing-order permissions, eight after-commit events, factories, and focused Pest coverage. No dependency, copyrighted/fandom-specific content, commit, or push was added.

## Baseline and migration evidence

- Initial branch/commit: `main` at `584fbfe285d77949604600270e741ce3cc20d368`.
- Prompt 7: committed; initial worktree clean; no pending migration.
- Initial MySQL rows: `viewing_progress=0`, `user_spoiler_preferences=0`.
- Migration: `2026_07_12_064234_implement_user_journey_foundation.php`, applied as batch 8 on loopback MySQL.
- SQL preview: additive creates/columns/indexes plus the reviewed old progress-unique replacement and deterministic row backfill.
- Empty isolated SQLite full forward and full rollback: passed.
- Non-empty isolated SQLite: one progress and one preference fixture preserved forward; scope became `episode:1`, legacy flag `1`, tolerance `warn`, warnings `false`; Prompt 8 rollback retained both rows and removed only new columns/tables.
- Local post-migration counts: every new personal/content table remains zero; no seed journey history was created.
- Permission seeder reran idempotently. No reset, refresh, wipe, or local rollback occurred.

## Security, privacy, and scope review

- All personal endpoints require Sanctum, verified email, named limit, and authenticated ownership. Public routes expose published viewing orders only.
- Personal visibility is forced private in this phase. No public profile, aggregate rating, favourite count, note, journey, session, progress, or event route exists.
- Progress and session idempotency are user scoped. Stale writes use stable HTTP 409. Runtime/basis points/backward movement are bounded.
- Note HTML is stripped, bodies are absent from list resources and audit metadata, and administrators have no implicit access.
- Search receives owner progress only at query time; shared projections, analytics, and ranking remain user-neutral.
- Account deletion cascades every identifiable current/historical User Journey row while leaving shared Catalog/Lore/Media/Search sources intact.
- Scope scan found no Community, messaging, watch rooms, synchronized playback, boards, quizzes, achievements, rankings, notifications, UI, NativePHP/mobile, provider sync, imports, scraping, AI/ML, fandom-specific content, or copyrighted asset.

## Known limits and risks

- Personal/public/follower projections remain deferred even though the visibility enum reserves future values; this phase always forces private.
- Portable foreign keys cannot express all cross-table universe/path invariants, so actions enforce and test them transactionally.
- High-volume event/session retention requires an approved operational/legal window before production launch.
- Rollback after multiple progress scopes exist intentionally stops rather than silently collapse data into the legacy one-row-per-work shape.
- Continue-watching ordering is relational and bounded; a dedicated projection should be considered only after measured query pressure.
- The future export endpoint/UI and legal-hold workflow remain deferred; the versionable export structure and deletion behavior are documented.

## Validation snapshot

- Focused User Journey suite: 24 tests, 115 assertions.
- Full Pest suite after implementation: 188 tests, 759 assertions.
- PHPStan: zero errors.
- Route inventory: 208 total; all 41 personal User Journey routes show `auth:sanctum`, `verified`, and `throttle:api-v1`; all three viewing-order reads show `api-v1-public` and optional Sanctum context.

Final passing commands:

```text
php artisan about
php artisan migrate:status
php artisan migrate --pretend --no-interaction
isolated SQLite full forward and full rollback
isolated SQLite non-empty legacy forward and Prompt 8 rollback
php artisan migrate --no-interaction
php artisan db:seed --class=Database\Seeders\RolePermissionSeeder --no-interaction
php artisan test --compact tests/Feature/UserJourney
php artisan test --compact
vendor/bin/phpstan analyse --no-progress
vendor/bin/pint --dirty --format agent
vendor/bin/pint --test --format agent
vendor/bin/pint
vendor/bin/pint --test
npm run lint
npm run format
npm run format:check
npm run types:check
npm run build
composer validate --strict
composer audit --no-interaction
npm audit
php artisan route:list -v
php artisan config:cache
php artisan route:cache
php artisan optimize:clear
git diff --check
git status --short
git diff --stat
```

Intermediate failures were not suppressed: the first isolated run caught an ineffective global PHP import; an existing spoiler test exposed a factory scope-key mismatch; early focused tests exposed the inferred `viewing_progresses` FK, non-public test ancestors, duplicate synthetic episode numbers, a missing preference morph alias, and continue-watching tie order; PHPStan decreased from 75 findings to zero through explicit model/action/resource contracts. Every affected check was rerun green.
