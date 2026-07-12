# Prompt 14 Authentication and Onboarding Baseline

Captured before Prompt 14 implementation on 2026-07-12.

## Repository evidence

- Branch: `main`.
- Commit: `30c06b423b1beea0d8939a1b431565353b9a1b23` (`Refactor onboarding and content workflow for the new MVP`).
- Prompt 13 is committed at the current commit. The handoff statement that Prompt 13 was uncommitted is stale; `git show HEAD` contains the Prompt 13 design-system, shell, audit, and documentation changes.
- Initial worktree: clean. `git status --short` returned no entries and `git diff --check` passed.
- Runtime: PHP 8.4.23, Laravel 13.19.0, local environment, MySQL at `127.0.0.1`, database `supernatural_db`, Reverb configuration, and FrankenPHP Octane.
- All 17 existing migrations were applied. No migration was pending before Prompt 14.

## Authentication and route evidence

- Fortify owns registration, login, logout, password reset, email verification/resend, password confirmation, two-factor challenge, and passkey routes.
- Existing authenticated web routes are Dashboard, Profile, Security, Appearance, and authorized no-content Moderation/Administration stubs.
- `EnsureVerifiedUserAccess` globally prevents unverified authenticated users from entering authenticated routes other than verification and logout routes.
- Fortify's configured successful destination is `/dashboard`.
- Prompt 13 already branded every existing authentication page through the shared auth layout, but form-level error summaries, the suspension page, and onboarding pages did not exist.
- No onboarding model, table, enum, route, middleware, controller, action, page, or completion flag existed at baseline.

## Domain persistence evidence

- `user_fandom_preferences` is the typed per-user/per-universe record for interest presence, preferred viewing order, and the supported private visibility defaults.
- `user_spoiler_preferences` stores `strict`, `warn`, or `permissive` tolerance, warning visibility, rewatch behavior, and optimistic-lock version.
- `viewing_progress` and append-only `viewing_progress_events` are updated through `RecordViewingProgress`; direct onboarding controller writes would duplicate domain behavior and are not permitted.
- `viewing_orders` has a public scope requiring a published, public, non-archived order under a published public universe.
- Catalog publication scopes exist for works, seasons, and episodes. Universe publication uses `status = published` plus `is_public = true`; universes do not have `published_at` or `archived_at` columns.
- Account deletion already cascades owner-domain foreign keys and performs explicit Community, restriction, block, and mute cleanup. The onboarding state must add a cascading user foreign key.

## Current data and safety decision

- Existing users: 2 (one verified and one unverified).
- Published public universes: 0.
- Published public viewing orders: 0.
- The onboarding empty-data path is therefore required for the current local database; no synthetic Catalog data will be added.
- Existing users must be backfilled as completed before onboarding middleware is attached. New Fortify registrations will receive an incomplete state inside the existing registration transaction.
- The database is local and the proposed migration is additive, non-destructive, and safe to continue after inspection. No reset, refresh, wipe, or production operation is authorized.

## Tooling note

Laravel Boost version-specific documentation search and database inspection were executed before implementation. An initial illustrative universe-count query failed because the prompt's proposed `published_at`/`archived_at` fields do not exist on `universes`; the corrected repository-schema query uses `status` and `is_public` and returned zero.
