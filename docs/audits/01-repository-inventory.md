# Repository Inventory

## Inventory Basis

This inventory describes the working tree captured at audit start on `main` / `ec856db74effbec97fe1bcad6a3bf30345237e92` on 2026-07-12 and reconciled against final `HEAD` `2f97c64067f0de93e61a68928bfd67e8aac9b23a`. An external process committed the previously dirty foundation during the audit; the auditor did not stage, commit or push. Ignored development artifacts are included where architecturally relevant.

## Top-Level Responsibilities

| Path | Responsibility | Current state |
| --- | --- | --- |
| `app/` | Laravel application code | Auth/settings starter only |
| `bootstrap/` | Laravel application and provider bootstrapping | Web, API, console, channel and health routing enabled |
| `config/` | Framework/package configuration | Standard Laravel plus Fortify, Inertia, Sanctum, Reverb and Octane |
| `database/` | Migrations, factory, seeder and ignored local SQLite | Auth/framework schema only |
| `resources/` | Inertia React UI, CSS and Blade root | Starter public/auth/dashboard/settings UI |
| `routes/` | Web, settings, API, channels and console | 48 total routes including vendor/framework routes |
| `tests/` | Pest tests | 39 passing tests focused on auth/settings |
| `.github/` | CI, Dependabot and duplicated agent skills | Test/lint workflows; Actions-only Dependabot |
| `public/` | Front controller and generic static icons | No fandom media; build output ignored |
| `storage/` | Runtime files | Ignored compiled views/logs; no product media |
| `.agents`, `.ai`, `.claude`, `.codex`, `.factory`, `.junie` | Agent tooling/instructions | Large duplicated skill/tool footprint; partly untracked |

## Backend Files and Modules

### Application actions and concerns

- `app/Actions/Fortify/CreateNewUser.php`: validates name/email/password and creates a user.
- `app/Actions/Fortify/ResetUserPassword.php`: validates and replaces a forgotten password.
- `app/Concerns/PasswordValidationRules.php`: shared password/current-password rules.
- `app/Concerns/ProfileValidationRules.php`: name/email rules and unique-email handling.

### Controllers

- `app/Http/Controllers/Controller.php`: empty Laravel base controller.
- `app/Http/Controllers/Settings/ProfileController.php`: profile display/update and immediate account deletion.
- `app/Http/Controllers/Settings/SecurityController.php`: password update plus passkey/2FA props.

No public content, API, admin, moderation, media, search, community, messaging, analytics, or webhook controllers exist.

### Form requests

- `PasswordUpdateRequest`: current-password plus confirmed new-password validation.
- `ProfileDeleteRequest`: current-password validation before deletion.
- `ProfileUpdateRequest`: current user's name/email validation.
- `TwoFactorAuthenticationRequest`: Fortify 2FA state validation trait.

### Middleware and providers

- `HandleAppearance`: exposes the appearance cookie to Blade.
- `HandleInertiaRequests`: shares app name, authenticated user and sidebar state.
- `AppServiceProvider`: immutable dates, production destructive-command prohibition, production password defaults.
- `FortifyServiceProvider`: Fortify actions, Inertia auth views, and login/2FA/passkey rate limiters.

### Models and domain support

- `User`: only application model; fillable name/email/password, hidden authentication secrets, hashed password cast, passkey/2FA traits.
- No API Resources, policies, gates, events, listeners, jobs, notifications classes, mailables, services, repositories, DTOs, enums, observers, traits beyond validation concerns, custom casts/scopes, or console command classes.

## Routes

### Application routes

| Method | URI | Purpose/security |
| --- | --- | --- |
| GET | `/` | Starter Inertia welcome page |
| GET | `/dashboard` | Authenticated plus `verified` middleware |
| GET/PATCH | `/settings/profile` | Authenticated profile display/update |
| DELETE | `/settings/profile` | Authenticated plus `verified`; password checked in request |
| GET | `/settings/security` | Authenticated, verified, password confirmation |
| PUT | `/settings/password` | Authenticated, verified, throttled |
| GET | `/settings/appearance` | Authenticated, verified |
| GET | `/.well-known/passkey-endpoints` | Public passkey management discovery |
| GET | `/api/user` | `auth:sanctum`; raw current user response |
| channel | `App.Models.User.{id}` | Private channel requiring matching user ID |

Fortify, Passkeys, Sanctum, local-storage, health, broadcast-auth, and local Boost routes bring the runtime total to 48. No versioned product API, admin route prefix, moderator route, content route, or community route exists.

## Database Inventory

### Models

| Model | Table | Relationships/features |
| --- | --- | --- |
| `User` | `users` | Has factory/notifications; passkeys and 2FA vendor traits; no soft deletes, roles, API-token trait, or email-verification contract |

The passkey model is supplied by Fortify. Infrastructure tables have no application models, which is expected.

### Migrations

| Migration | Tables/changes | Important constraints |
| --- | --- | --- |
| `0001_01_01_000000_create_users_table.php` | users, password reset tokens, sessions | Unique user email; session user ID indexed but no FK |
| `0001_01_01_000001_create_cache_table.php` | cache, cache locks | Primary keys and expiration indexes |
| `0001_01_01_000002_create_jobs_table.php` | jobs, batches, failed jobs | Queue index; unique failed-job UUID; compound failure index |
| `2024_01_01_000000_create_passkeys_table.php` | passkeys | User FK/cascade delete; unique credential ID; JSON credential |
| `2025_08_14_170933_add_two_factor_columns_to_users_table.php` | user 2FA fields | Nullable encrypted text/timestamp fields |
| `2026_07_11_185116_create_personal_access_tokens_table.php` | Sanctum tokens | Morph index; unique token; expiry index; currently untracked |

No fandom/content/community/moderation/media/audit schema exists. There are no JSON columns except passkey credential and no polymorphic product relationship except Sanctum's token owner.

### Factory and seeder

- `UserFactory`: verified user by default; known development password `password`; unverified and 2FA states.
- `DatabaseSeeder`: creates one `Test User` at `test@example.com`; no environment guard or product demo-data strategy.

## Frontend Files and Modules

### Entry and server root

- `resources/js/app.tsx`: creates Inertia v3 app, selects layouts, installs tooltip/toast providers, initializes theme, and globally configures Echo/Reverb.
- `resources/views/app.blade.php`: root document, theme boot script, Vite/React/Inertia directives.
- `resources/css/app.css`: Tailwind v4 imports, CSS-variable tokens, light/dark palette and base styles.

### Pages

- Public: `pages/welcome.tsx` (44 kB Laravel starter page).
- Fan shell: `pages/dashboard.tsx` (placeholder skeleton cards).
- Auth: login, register, forgot/reset password, verify email, confirm password, 2FA challenge.
- Settings: profile, security, appearance.
- Admin/moderation: none.

### Layouts

- `app-layout.tsx`, `app/app-header-layout.tsx`, `app/app-sidebar-layout.tsx`.
- `auth-layout.tsx` plus card/simple/split variants.
- `settings/layout.tsx`.
- Welcome deliberately receives no persistent layout.

### Reusable application components

- Shell/navigation: app shell/header/sidebar/logo, breadcrumbs, nav main/footer/user, user menu.
- Auth/security: delete user, password input, passkey management/registration/verification, 2FA management/setup/recovery codes.
- Feedback/content: heading, input error, alert error, appearance tabs, text link, user info.

### UI primitives

The UI folder includes alerts, avatars, badges, breadcrumbs, buttons, cards, checkboxes, dialogs, dropdowns, forms/fields, OTP, labels, menus, navigation, sheets/sidebar, skeletons/spinners, toasts, tooltips, tables/charts, carousel/calendar, resizable panels, chat/message/attachment primitives, and more.

The following 34 primitives have no import outside `resources/js/components/ui` in the audited tree and are apparently premature/unused: attachment, badge, bubble, button-group, calendar, carousel, chart, collapsible, combobox, context-menu, direction, drawer, empty, field, hover-card, icon, item, kbd, marker, menubar, message-scroller, message, native-select, pagination, popover, progress, radio-group, resizable, scroll-area, select, slider, switch, table, and toggle-group.

### Hooks and types

- Hooks: appearance, clipboard, current URL, flash toast, initials, mobile navigation, 2FA, and two competing `use-mobile` implementations.
- `use-mobile.ts` is untracked, uses an effect/set-state implementation, fails lint/format, and shadows the tracked `use-mobile.tsx` implementation.
- Types: auth, navigation, UI, global and Vite environment declarations.
- State management: React/local hooks and Inertia props only; no external store.

### Generated files

`resources/js/actions`, `resources/js/routes`, and `resources/js/wayfinder` are generated by Wayfinder and ignored. They correctly provide typed controller and route helpers but should not be hand-edited or treated as source-of-truth modules.

## Tests

### Feature tests

- Authentication: login/logout, registration, password reset/confirmation, email verification routes/notifications, 2FA challenge.
- Settings: profile update/delete, password/security behavior.
- Dashboard access.
- Starter example test.

### Unit tests

- Starter example test only.

### Missing suites

No browser, frontend/component, E2E, API contract, architecture, authorization policy, real-time, accessibility, performance, security, visual-regression, snapshot, or mobile tests. No coverage threshold/configuration is present.

## Integrations and Configuration

| Integration | Evidence | Actual application use |
| --- | --- | --- |
| Fortify/passkeys | config, provider, actions, UI, tests, migrations | Active |
| Inertia | middleware, controllers, Vite, React pages | Active |
| Wayfinder | Vite plugin and generated imports | Active |
| Sanctum | config, migration, `/api/user` guard | Partial; no token issuance trait/flow |
| Reverb/Echo | config, channel, React configuration | Infrastructure only; no broadcast events |
| Octane | package/config; local driver FrankenPHP | Runtime infrastructure only |
| MCP | Composer dependency | No tool/resource/prompt/server code |
| Chisel | Composer dependency | No application reference |
| S3 | standard filesystem config | No product storage code |
| Mail | Laravel/Fortify reset and verification notifications | Auth infrastructure |

Configuration files cover application, auth, broadcast, cache, database, filesystem, Fortify, Inertia, logging, mail, Octane, queue, Reverb, Sanctum, services and sessions. No CORS file, search, media, permissions, analytics, monitoring, or content-provider configuration exists.

## Build and Dependency Configuration

- Composer: PHP `^8.3`, Laravel 13, stable minimum, optimized autoloading, setup/dev/lint/type/test scripts.
- npm: npm 11.18 package manager, Vite, Inertia, React Compiler, Tailwind, Wayfinder, ESLint, Prettier and TypeScript.
- Component dependency overlap: `@base-ui/react`, aggregate `radix-ui`, and 11 individual `@radix-ui/react-*` packages are all direct dependencies.
- Zero known Composer/npm production advisories were reported during the audit.
- Several product/runtime packages are pre-1.0 (MCP, Chisel, Wayfinder, Passkeys, shadcn React); their contracts should be treated as potentially unstable.

## CI/CD Files

- `.github/workflows/tests.yml`: PHP 8.3-8.5 matrix; npm/Composer install, environment key, Vite build, PHPStan, Pest.
- `.github/workflows/lint.yml`: mutating Pint/Prettier/ESLint commands and write permission; no commit and no `*:check` scripts.
- `.github/dependabot.yml`: weekly grouped GitHub Actions updates only; Composer/npm ecosystems are absent.
- No deployment, release, Docker, queue, scheduler, Reverb, backup, monitoring, or infrastructure-as-code files.

## Public Assets

- `public/favicon.ico`, `favicon.svg`, `apple-touch-icon.png`: generic application/Laravel starter icons.
- `public/robots.txt`, `.htaccess`, `index.php`: framework web assets.
- `public/build`: ignored generated Vite output.
- No committed photographs, Supernatural marks, posters, screenshots, audio, video, transcripts, 3D models, or custom licensed fonts.
- Vite downloads Instrument Sans through the Bunny fonts integration at build time; generated font files are ignored.

## Potentially Sensitive Files

| Path/category | Status |
| --- | --- |
| `.env` | Present locally and ignored; values not inspected/reported |
| `.env.example` | Tracked; variable names/default development configuration only |
| `.idea`, `.vscode` | Local IDE state ignored |
| `database/database.sqlite` | Local database ignored; not inspected |
| `storage/logs`, compiled views | Ignored runtime output; not included in scans/report |
| certificates/private keys | None tracked or found in application scan/history |
| cloud keys/tokens | None found in application scan/history |
| absolute user-machine paths | None found in repository/application files |

Test/factory literals (`password`, `test@example.com`) are public demo credentials, not secrets, but must be guarded from production seeding.

## Potentially Copyrighted or Trademarked Files

No potentially copyrighted Supernatural media or copied franchise text exists. Generic starter icons and source code remain subject to their upstream/project licenses. The `supernatural` repository/folder name and eventual theme may implicate trademark/fan-use review, but there is currently no brand artwork to remove.

## Documentation Inventory

Before this audit there was no project README, setup guide, architecture guide, API documentation, license file, code of conduct, contribution guide, security policy, changelog, release guide, content policy, copyright/takedown policy, privacy documentation, or ADR/decision log. The repository is publicly visible at `vipertecpro/supernatural`. Agent instruction files and duplicated skill references are development tooling, not public project documentation.
