# Prompt 3 Architecture Baseline

Date: 2026-07-12 (Asia/Kolkata)  
Repository: `vipertecpro/supernatural`

## Git and working tree

- Branch: `main`
- Commit: `2f97c64067f0de93e61a68928bfd67e8aac9b23a` (`first commit`)
- Prompt 2 committed: **No.** `HEAD` is unchanged from Prompt 2's starting commit. Its application, configuration, migration, test, governance, and architecture work is modified or untracked in the working tree.
- Prompt 3 starting tree: heavily dirty by design. Prompt 1 documentation is staged; Prompt 2 work is unstaged/untracked. Prompt 3 must preserve both sets and may write only under `docs/architecture`, `docs/project`, and `docs/audits`.

## Verified runtime and foundation

Laravel Boost and `php artisan about` report PHP 8.4.23, Laravel 13.19.0, MySQL, Inertia 3.1.1, Sanctum 4.3.2, Reverb 1.10.2, Octane 2.17.5 with FrankenPHP, React 19.2.7, and Pest 4.7.5. The route list contains 51 routes including `/api/v1/health`, verified `/api/v1/me`, verified broadcasting authorization, and verified moderator/administrator capability boundaries.

Prompt 2's working-tree foundation contains:

- `users`, first-party `roles`, `permissions`, `role_user`, and `permission_role` using unsigned bigint identifiers;
- privacy-conscious append-only `audit_logs` with nullable morph subject and request ID;
- `universes`, `sources`, nullable `content_licenses`, and polymorphic `spoiler_constraints`;
- enum-backed roles, permissions, publication status, source type, and spoiler severity;
- policies for universe, source, license, and audit access;
- verified-email enforcement, Sanctum API v1 envelope, request correlation, named throttles, and hardened optional Reverb.

## Live-schema drift

The read-only live MySQL schema contains only the pre-Prompt-2 framework/authentication tables. It does **not** contain `roles`, `permissions`, `permission_role`, `role_user`, `audit_logs`, `content_licenses`, `universes`, `sources`, or `spoiler_constraints`. Therefore the following three migrations remain pending locally:

- `2026_07_11_202441_create_authorization_tables.php`
- `2026_07_11_202441_create_audit_logs_table.php`
- `2026_07_11_202441_create_fandom_foundation_tables.php`

No migration was run during Prompt 3. Migration files and tests, rather than the developer database, are the schema-design baseline.

## Preserved conventions

- standard unsigned bigint primary and foreign keys;
- UTC timestamps, enum casts, factories, policies, and API Resources;
- `created_by`/`updated_by` nullable user references for editorial roots;
- unknown rights represented by nullable permission values, never implicit permission;
- backend policies/gates as authority; Sanctum abilities only narrow tokens;
- polymorphism only for deliberately cross-cutting attachments (audit, spoilers, citations, media, moderation subjects), with an explicit morph map required before expansion;
- database persistence as truth and Reverb only as transport;
- Supernatural as data, not a schema discriminator or tenant.

## Unresolved risks

The root software license, production retention/legal-hold policy, deletion/export policy, deployment/backup/monitoring, search engine selection, push provider, and media processing/storage provider remain owner or later-phase decisions. Prompt 2 is uncommitted, so accidental loss and reviewability remain high risks. No administrator account is seeded.

## Decision

Architecture design may safely continue. Prompt 2 materially matches its stabilization report, with two recorded qualifications: it is uncommitted and its three domain/foundation migrations are not applied to the local database.
