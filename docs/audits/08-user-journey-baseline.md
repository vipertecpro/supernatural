# Prompt 8 User Journey Baseline

Date: 2026-07-12 (Asia/Kolkata)

## Repository and preservation

- Branch: `main`.
- Commit: `584fbfe285d77949604600270e741ce3cc20d368` (`Implement prompt 7 media and search foundation`).
- Prompt 7 is committed at the baseline commit. Its migration is applied as batch 7.
- Initial worktree: clean. Initial `git diff --check`: passed.
- No pending migration existed before Prompt 8.
- No existing user change required preservation.

## Runtime and database safety

- Laravel 13.19.0 on PHP 8.4.23 in `local` mode.
- MySQL is configured on loopback `127.0.0.1` with the development database `supernatural_db`.
- Queue, cache, and session use database-backed drivers. Octane uses FrankenPHP.
- Existing `viewing_progress` rows: 0.
- Existing `user_spoiler_preferences` rows: 0.
- All migrations through Prompt 7 were applied. No reset, refresh, wipe, or local rollback was run.
- The implementation could safely continue with an additive migration and isolated SQLite rollback testing.

## Existing schema

`viewing_progress` initially contained user, universe, work, optional season, optional episode, and timestamps. It enforced one row per user/work and represented the highest known Catalog path used by the spoiler evaluator.

`user_spoiler_preferences` initially contained user, universe, tolerance, warning choice, and timestamps with one row per user/universe.

Both tables are preserved and evolved in place. The migration is required to retain legacy path semantics and existing tolerance values.
