# Prompt 5 Editorial Governance Baseline

Date: 2026-07-12 (Asia/Kolkata)  
Repository: `vipertecpro/supernatural`

## Git and preservation state

- Branch: `main`
- Commit: `bc44d9f6136330dc38e800de52ca551c8afb8900` (`Harden app bootstrap and CI with auth and API defaults`)
- Prompt 1–4 committed: **Yes.** The current commit contains the foundation, architecture, six-table Catalog Core, documentation, migrations, factories, policies, API routes, and tests from those prompts.
- Initial working tree: clean; `git status --short` returned no entries.
- Initial `git diff --check`: passed.
- Recent history: `bc44d9f`, `2f97c64`, `ec856db`, `449cdba`, `adb71df`, `3a31bac`.

## Runtime and database safety

`php artisan about` reported Laravel 13.19.0, PHP 8.4.23, local environment, MySQL, database-backed cache/queue/session, Reverb broadcasting, and FrankenPHP Octane. The selected database is `supernatural_db` on the loopback host `127.0.0.1`; it is clearly a local development database.

All existing migrations were applied. No pending migration was found. The database schema contains the Prompt 2 foundation and Prompt 4 Catalog tables, including the existing `works.lock_version` and legacy JSON-backed `spoiler_constraints.earliest_progress` field.

No destructive migration command was run. Prompt 5 can safely continue additively while preserving existing development data. New migrations will be inspected before local execution and rollback will be verified only against an isolated temporary database.

## Initial implementation risks

- Optimistic locking currently covers only works; franchises, work translations, seasons, and episodes need additive version columns and all direct lifecycle actions need compare-and-increment semantics.
- Legacy spoiler severities use `none`, `mild`, `major`, and `critical`; the accepted architecture uses `none`, `minor`, `moderate`, `major`, and `finale`. Existing values must be mapped without discarding constraints.
- Existing content-license booleans preserve unknown through `NULL`, but attributable rights history does not yet exist.
- Existing public resources conservatively redact on missing or unsafe classification; normalized boundaries and viewer context do not yet exist.
- Catalog publication currently predates revision approval. Prompt 5 will preserve publication as a distinct action while enforcing version checks and the approved editorial path for applied changes.
