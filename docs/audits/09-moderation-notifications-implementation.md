# Prompt 9 Moderation and Notifications Implementation Audit

## Baseline and migration evidence

- Baseline branch: `main`.
- Baseline commit: `9c3d62fe0c8ae241f4123a683abcacdfad7dab27` (`Add journey-aware viewing progress and search ranking`).
- Prompt 8 was committed at the baseline commit.
- The initial working tree was clean and `git diff --check` passed.
- `php artisan migrate:status` showed no pending migrations before Prompt 9.
- Created one additive migration: `2026_07_12_130000_implement_moderation_notifications.php`.
- The migration was applied locally in batch 9. It performs no backfill and did not change existing application rows.
- A fresh in-memory SQLite database completed a full forward migration and a full rollback. The check was repeated after moving the migration after the existing media migration so `report_evidence.media_asset_id` is safe on a fresh database.
- The final local migration status reports every migration as applied.

## Persistence selected from the canonical architecture

Fourteen tables were added:

1. `report_categories`
2. `reports`
3. `report_evidence`
4. `moderation_cases`
5. `moderation_case_assignments`
6. `moderation_actions`
7. `user_restrictions`
8. `user_restriction_scopes`
9. `content_restrictions`
10. `appeals`
11. `appeal_decisions`
12. `notifications`
13. `notification_preferences`
14. `notification_deliveries`

The schema follows the canonical Identity ownership of user restrictions and the canonical Notifications ownership of notification persistence. Notification types remain a code-controlled registry, so no `notification_types` table was added. Copyright notices, trust signals, digest preferences, and push devices remain deferred.

Fourteen Eloquent models and factories were added for the tables above. The notification model is named `UserNotification` to avoid collision with Laravel's notification namespace. Eighteen backed enums define report, evidence, case, assignment, action, restriction, appeal, notification, preference, and delivery states.

## Moderation implementation

- `ReportCategorySeeder` provides twelve stable, enabled category keys and is safely repeatable.
- Authenticated, verified users can report only targets in the explicit morph registry. Target visibility is checked, duplicate open reports are rejected, and strict rate limiting is applied.
- Reporters can list and inspect their own reports, add allowlisted evidence, and withdraw eligible reports. Evidence accepts bounded text, URL, or existing-media references; visibility and ownership rules prevent private evidence leakage.
- Authorized moderators can create cases, link reports, assign reviewers, move cases through explicit states, and apply append-only actions. Cases have non-sequential public ULIDs.
- Assignments retain status and assignment history. A subject cannot review their own case and the action service rejects moderator self-dealing.
- Moderation actions and appeal decisions are immutable records. Rights-authority and permanent-account actions require their dedicated permissions.
- User restrictions are centrally evaluated by active dates, status, type, and scoped capabilities. Unique active keys and transactional checks reduce duplicate races. Lift operations preserve the historical row.
- Content restrictions can hide content publicly, remove it from search, freeze editing, block attachment, or represent a takedown. Lifting a restriction queues reprojection where appropriate.
- Enforcement was integrated into catalog, lore, timeline, media, editorial revision, search projection, and protected API entry points. Public parent and child queries omit active public-hide/takedown content. Private Journey records remain private and available to their owner unless the account has a platform suspension.
- Appeals can be created only by the affected user for an eligible action or restriction, are rate limited, and enforce one active appeal for the same subject. Users can inspect and withdraw their own appeals. Authorized reviewers can decide appeals, with reviewer-conflict checks and immutable decisions.
- Closing a case closes linked reports and emits report lifecycle events. Audit history is retained in actions, assignments, restrictions, appeals, decisions, and delivery attempts without storing request dumps or provider response bodies.

## Notification implementation

- `NotificationTypeRegistry` contains twelve initial stable types: report received/closed, moderation action applied, restriction lifted, appeal received/decided, case assigned, editorial revision approved/applied, media approved, Journey completed, and rewatch completed.
- Each type has schema version 1, an allowlist of scalar payload keys, recipient resolution, default priority/channels, and a trusted internal route key. Nested payloads, unsafe field names, arbitrary URLs, and arbitrary notification types are rejected.
- Creation uses a recipient-and-idempotency key constraint. Mandatory in-app records are independent of optional email delivery.
- Rendering is recipient-aware and produces detailed, warning, redacted, or unavailable output based on spoiler preferences, target visibility, content restrictions, and deletion state. Raw payloads and arbitrary links never reach API resources or email templates.
- Users can list, inspect, mark read/unread, archive, and mark all of their own notifications read. Cross-user access is policy protected.
- Preferences are per type and channel. Mandatory in-app notifications cannot be disabled; optional email can be enabled or suppressed.
- Delivery attempts have bounded state transitions and a maximum of three attempts. They do not persist addresses, rendered provider bodies, secrets, or provider responses.
- Email uses Laravel's queued notification path through `StableNotificationMail`. The database row is the durable product record; email is optional and retry bounded. SMTP acceptance is not proof of final provider delivery and remains an operational risk.
- Existing events consumed are `EditorialRevisionApproved`, `EditorialRevisionApplied`, `MediaPublished`, `ViewingJourneyCompleted`, and `RewatchCycleCompleted`.
- Added scalar, after-commit events are `ReportSubmitted`, `ReportClosed`, `ModerationCaseAssigned`, `ModerationActionApplied`, `UserRestrictionApplied`, `UserRestrictionLifted`, `ContentRestrictionApplied`, `ContentRestrictionLifted`, `AppealSubmitted`, and `AppealDecided`.
- Progress, session, playback-position, search, and private-note events do not create notifications. No personal activity or notification data is broadcast over Reverb.

## Authorization, APIs, privacy, and retention

- Added granular report, moderation-case, assignment, action, restriction, appeal, and notification permissions. Moderator defaults exclude permanent-account and rights-authority powers; administrators receive all permissions.
- Policies cover reports, cases, appeals, notifications, notification preferences, and existing restricted content modules. Routes require Sanctum, verified email, policies, the API limiter, and centralized restriction middleware; report and appeal writes have additional limiters.
- Added 29 API routes, taking the route count from 208 to 237. They cover report categories, own-report workflows, moderator case/action/restriction workflows, own appeals, moderator appeal decisions, own notifications, and notification preferences.
- Reporter identity is excluded from ordinary resources and is disclosed only to an authorized moderator through the moderation resource path. Private notes, legal notes, raw payloads, provider data, viewing history, playback position, and Journey internals are never exposed by these endpoints.
- Account deletion nulls report subjects and actor references where history must survive, detaches appeal restriction references before restriction cascade, and cascades user-owned notifications, preferences, and deliveries. Existing private Journey deletion behavior is unchanged.
- Retention is stateful rather than destructive for moderation history. User-facing archive/withdraw/lift operations preserve audit evidence; no new background purge policy was invented.

## Tests

Four focused Pest feature files were added:

- `tests/Feature/Moderation/ModerationWorkflowTest.php`
- `tests/Feature/Moderation/ModerationApiTest.php`
- `tests/Feature/Moderation/ModerationEnforcementTest.php`
- `tests/Feature/Notifications/StableNotificationTest.php`

They cover category seeding, report validation and ownership, evidence visibility, case assignments, immutable actions and decisions, restriction application/expiry/lift/enforcement, reviewer conflicts, appeals, notification registry validation, idempotency, spoiler-safe rendering, preferences, retry limits, route authorization, privacy, and account deletion. The final full suite passes 221 tests with 920 assertions; the focused Prompt 9 suite passes 33 tests with 146 assertions.

## Validation executed

The following checks were executed rather than inferred:

```text
php artisan about
php artisan migrate:status
php artisan migrate --pretend
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan migrate --force
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan migrate:rollback --force
php artisan migrate --no-interaction
php artisan db:seed --class=RolePermissionSeeder --no-interaction
php artisan db:seed --class=RolePermissionSeeder --no-interaction
php artisan db:seed --class=ReportCategorySeeder --no-interaction
php artisan db:seed --class=ReportCategorySeeder --no-interaction
php artisan test --compact tests/Feature/Moderation tests/Feature/Notifications
php artisan test --compact
vendor/bin/phpstan analyse --no-progress
vendor/bin/pint --dirty --format agent
vendor/bin/pint --format agent
vendor/bin/pint --test --format agent
npm run lint
npm run format
npm run format:check
npm run types:check
npm run build
composer validate --strict
composer audit
npm audit
php artisan route:list -v
php artisan config:cache
php artisan route:cache
php artisan optimize:clear
git diff --check
```

The SQLite forward/rollback sequence and migration status were repeated after correcting the migration timestamp. Seeders were deliberately run twice to verify idempotency.

Intermediate failures were not suppressed:

- The first PHPStan pass reported 101 Eloquent enum/array typing issues; model PHPDoc and generic method contracts were corrected. A later pass reported four, then two, related typing issues before reaching zero errors.
- The first focused test pass exposed a missing generated case `public_id`, an immutability/test enum mismatch, and an API 500. These were fixed and retested.
- A later focused pass found an invalid string argument to `assertJsonMissing`; the privacy assertion was corrected.
- The new report-close test initially lacked its `Report` import; the import was added and the suite rerun.
- The migration was initially generated before the media migration, which could make a fresh MySQL migration fail on its media foreign key. It was renamed to `2026_07_12_130000_...` before local application, and fresh forward/rollback validation was rerun.

No final validation failure remains.

## Change inventory and scope review

- Created files: 105.
- Modified tracked files: 51.
- Added dependencies: none.
- Created code is confined to moderation/notification domain actions and services, enums, events, API controllers/requests/resources/middleware, listeners, models, policies, factories, one migration, seed data, tests, configuration, and Prompt 9 documentation.
- Modified code integrates enforcement with existing models, policies, public child queries, editorial/media actions, search projection, providers, routes, seeders, and canonical architecture/policy documents.
- The final scope scan found no Community, posts, comments, reactions, followers, Bunkers, messaging, watch rooms, case-board UI, gamification, public profiles, moderator frontend, notification frontend, push, mobile/NativePHP, AI moderation, external moderation provider, device fingerprinting, progress broadcasting, or streaming integration implementation.
- No copyrighted assets, copyrighted text, or Supernatural-specific content was added.
- Nothing was committed, staged, or pushed.

## Deviations, residual risks, and Prompt 10 recommendation

There is no known architecture deviation. The prompt's broader candidate schema was narrowed to the canonical architecture: notification types are code-controlled, user restrictions stay Identity-owned, and copyright/trust/push/digest tables remain deferred.

Remaining moderation risks are operational policy calibration, reviewer staffing and separation of duties, legal retention requirements, high-volume abuse heuristics, and search-index convergence after restriction changes. Remaining notification risks are SMTP handoff observability, queue operations, rendering-copy evolution across schema versions, email deliverability, and future digest/push consent handling.

Deferred moderation capabilities are copyright-notice workflows, trust signals, automated/AI moderation, external moderation providers, community/message moderation, and moderator frontend tooling. Deferred notification capabilities are digest scheduling/preferences, push devices and delivery, frontend notification UI, mobile integration, provider webhooks, and Reverb delivery.

The repository is ready for Prompt 10 at the code, schema, test, static-analysis, asset-build, dependency-audit, route/cache, privacy, and scope-control gates. Prompt 10 should begin only after reviewing this audit and the canonical implementation document, then treat the fourteen Prompt 9 tables, code-controlled notification registry, centralized restriction evaluator, immutable moderation ledger, and private Journey boundary as stable contracts. Prompt 10 must not bypass those contracts or reinterpret deferred capabilities as already implemented.
