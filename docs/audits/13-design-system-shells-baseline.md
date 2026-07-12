# Prompt 13 Design System and Shells Baseline

Captured before Prompt 13 implementation on 2026-07-12.

## Repository evidence

- Branch: `main`.
- Commit: `2665967a1785f2b6830e4cf1d3d4b00956a86a0d` (`Add community moderation and notification support`).
- Prompts 10, 11, and 12 were committed together in that commit. The Prompt 12 source documents and Prompt 10/11 implementation files are present in the commit.
- Initial worktree: clean. `git status --short` returned no entries and `git diff --check` passed.
- Runtime: PHP 8.4.23, Laravel 13.19.0, local MySQL, Inertia 3, React 19, Tailwind CSS 4, Wayfinder, Reverb configuration, and FrankenPHP Octane.
- Routes: 292 total, including 243 `/api/v1` routes. `/moderation` and `/administration` are authorized no-content stubs, not Inertia pages.

## Frontend baseline

- 12 Inertia pages, 8 layouts, 57 shadcn UI primitives, 27 top-level custom components, and 8 hooks.
- Existing scripts: `build`, `build:ssr`, `dev`, `format`, `format:check`, `lint`, `lint:check`, and `types:check`.
- Existing light/dark/system appearance persisted through local storage and an appearance cookie.
- Existing CSS variables were generic shadcn neutrals with a red primary; no product semantic state families or layout/motion tokens existed.
- The welcome page was the Laravel starter page, including Laravel branding and external starter links.
- The authenticated app had a responsive shadcn sidebar, user menu, breadcrumbs, and settings nesting, but only one live destination: Dashboard.
- Inertia shared only `name`, `auth.user`, and `sidebarOpen`; it did not expose route-safe workspace navigation.
- Existing accessibility assets included Radix focus management, labelled forms, breadcrumbs, responsive Sheet/Sidebar behavior, and appearance initialization. Skip links, offline/conflict/spoiler states, forced-colour handling, and a documented shell hierarchy were absent.

## Safety decision

Implementation could continue safely without routes, migrations, dependencies, or product-domain data. Existing auth behavior, generated Wayfinder files, no-content workspace stubs, and backend authorization were preserved.
