# Prompt 10 Community and Bunkers Baseline

Date: 2026-07-12 (Asia/Kolkata)

## Repository safety

- Branch: `main`.
- Commit: `1e3beb92ca04008e69e29474dbd8ced10d04a47d` (`Add moderation restriction handling and privacy policy updates`).
- Prompt 9 is committed at the baseline commit; its migration is applied as batch 9.
- Initial worktree: clean. Initial `git diff --check`: passed.
- No migration was pending before Prompt 10.
- No existing work required preservation beyond committed history.

## Runtime and database

- Laravel 13.19.0, PHP 8.4.23, local environment, FrankenPHP Octane.
- Database driver: MySQL-compatible MariaDB 10.11.13.
- Database: `supernatural_db` on loopback host `127.0.0.1`; this is clearly a local development database.
- Existing users: 2. Existing Community tables: none.
- Existing Catalog, Lore, Editorial, Media, Search, User Journey, Moderation, Notification, and audit migrations were applied.
- Additive migration work could safely continue after SQL review and isolated SQLite forward/rollback validation.

## Reused contracts

- Reportable morphs already covered public Catalog, Lore, Media, viewing-order, and user targets.
- Media attachments already used an enforced morph map and allowed Catalog/Lore targets.
- Notifications used a code-owned registry, scalar payload validation, recipient-aware spoiler rendering, and queued after-commit delivery.
- `RestrictionEvaluator`, `HasModerationRestrictions`, `SpoilerVisibilityService`, normalized spoiler constraints, `AuditLogger`, v1 envelopes, cursor pagination, named limits, and optimistic locking were present.
- Account deletion already removed private Journey and notification data while retaining minimized moderation history.

## Baseline conclusion

Prompt 10 could proceed additively. Community had to extend these registries and evaluators rather than duplicate them, and it could not begin Messaging or Reverb delivery.
