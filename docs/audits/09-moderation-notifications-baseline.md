# Prompt 9 Moderation and Notifications Baseline

Date: 2026-07-12 (Asia/Kolkata)

## Repository and preservation

- Branch: `main`.
- Commit: `9c3d62fe0c8ae241f4123a683abcacdfad7dab27` (`Add journey-aware viewing progress and search ranking`).
- Prompt 8 is committed at the baseline commit. Its migration is applied as batch 8.
- Initial worktree: clean. Initial `git diff --check`: passed.
- No pending migration existed before Prompt 9.
- No existing work required preservation beyond keeping the committed repository history intact.

## Runtime and database safety

- Laravel 13.19.0 on PHP 8.4.23 in `local` mode.
- MySQL-compatible storage is MariaDB 10.11.13, configured as database `supernatural_db` on loopback host `127.0.0.1`.
- Queue, cache, and session use database-backed drivers. Octane uses FrankenPHP.
- Mail uses the Laravel `smtp` transport. Prompt 9 tests must fake mail and queues; no real message may be sent.
- All migrations through Prompt 8 are applied. No reset, refresh, wipe, or local rollback was run.
- The implementation can safely continue with additive migrations, reviewed SQL, and isolated SQLite forward/rollback validation before local execution.

## Existing moderation and notification state

- No table matching reports, moderation, restrictions, appeals, or application notifications exists.
- No Laravel database-notification table or application inbox model exists.
- The `User` model uses Laravel's `Notifiable` concern for framework authentication mail such as email verification and password reset only.
- No application notification type registry, preference store, delivery-attempt store, or moderation workflow exists.
- No existing restriction evaluator or moderation middleware exists.
- Existing scalar after-commit events include editorial, media/search, and User Journey lifecycle events. Viewing progress and session activity are not broadcast.

## Existing integration boundaries

- Authorization is database-backed through `RolePermissionSeeder`, `PermissionName`, gates, and record policies.
- `AuditLogger` stores sanitized identifiers/state and request IDs; it must not receive report text, appeal text, private notes, rendered notifications, or delivery payloads.
- `Relation::enforceMorphMap()` supplies stable aliases for existing Catalog, Lore, Editorial, Media, Search, and User Journey records.
- Public Catalog/Lore/Media visibility and Search projection behavior already have source-module actions and after-commit projection events; Prompt 9 must integrate through those boundaries instead of replacing publication, rights, takedown, or spoiler state.
- Account deletion currently cascades owner-private User Journey data and deletes the user. Prompt 9 must define report anonymization and notification/preference deletion without preserving private Journey data.

## Baseline conclusion

Prompt 9 may proceed. The repository is clean, Prompt 8 is committed, the configured database is clearly local, no migration is pending, and the selected work can be implemented additively without deleting or rewriting existing Catalog, Lore, Editorial, Media, Search, or User Journey records.
