# Prompt 12 UI/UX Baseline

Captured before Prompt 12 documentation changes on 2026-07-12.

## Repository state

| Check | Evidence |
| --- | --- |
| Branch / commit | `main` / `1e3beb92ca04008e69e29474dbd8ced10d04a47d` |
| Prompt 10 / 11 committed | No. Both implementations remain uncommitted and were preserved. |
| Working tree | Dirty with Prompt 10 Community/Bunkers and Prompt 11 Interaction Safety plus their docs/tests. |
| Diff integrity | `git diff --check` passed. |
| Runtime | Laravel 13.19.0, PHP 8.4.23, local MySQL, Inertia, Reverb configured, FrankenPHP Octane. |
| Routes | 292 total; 243 under `/api/v1`; no Messaging routes. |
| Safe to plan | Yes. Prompt 12 writes docs only and requires no migration, dependency, build, or runtime mutation. |

## Frontend stack

- Inertia 3, React 19, TypeScript 5.7, Vite 8, React Compiler plugin.
- Tailwind CSS 4 CSS-first configuration and semantic CSS variables in `resources/css/app.css`.
- shadcn new-york style configured for Radix primitives, Lucide icons, `@/` alias, and 57 installed UI component files.
- Base UI is also installed and several newer components may use it internally; project `components.json`/shadcn info currently reports Radix as the configured base.
- Sonner, TanStack Table, Recharts, Embla, date-fns, Vaul, next-themes, React DayPicker, and resizable panels.
- Echo/Reverb packages are installed and configured only when the environment flag is enabled; no product realtime UI exists.
- No approved motion/GSAP/Three/React Three Fiber/WebGL dependency. No Storybook, Vitest/Jest, Testing Library, Playwright/Cypress, or axe package is configured.

## Existing screens and routes

There are **12 Inertia pages**:

- Public: `welcome.tsx` at `/`.
- Auth: login, registration, verification notice, forgot/reset password, password confirmation, and two-factor challenge.
- Authenticated: placeholder `dashboard.tsx`.
- Settings: profile, security/passkeys/2FA, and appearance.

There are 8 layout files covering app sidebar/header variants, auth variants, and settings nesting. `/moderation` and `/administration` are verified/capability-protected no-content closures—not screens. There are no public Catalog/Lore/Search/Community pages, Fan Journey screens, contributor workspace, moderator queue, or administration workspace.

## Reusable frontend assets

- **57 UI primitives:** buttons, fields, inputs/selects/combobox, dialog/sheet/drawer, navigation/sidebar/breadcrumb, table/pagination, empty/skeleton/spinner/progress, chart, carousel, Sonner, and generic message/bubble/attachment primitives.
- **27 custom components:** app shell/header/sidebar, breadcrumbs/navigation/user menu, theme controls, profile deletion, passkey/2FA management, form errors, headings, and helpers.
- **8 hooks:** appearance, clipboard, current URL, flash toast, initials, mobile navigation/viewport, and two-factor behavior.
- Generated Wayfinder action and route functions cover current web/API routes and are actively imported by existing pages.

## Current design and maturity

The authenticated starter has a coherent responsive shadcn shell, semantic token layer, dark/light/system appearance, focus styles, labelled dialogs, responsive sidebar sheet, skeletons, empty states, Sonner, and accessible auth/security patterns. This is a useful **component foundation**, not a product design system.

The welcome page remains Laravel starter content with Laravel branding, raw colour values, inline SVG illustration, external Laravel/Laracasts/Cloud links, and no product narrative. The default token palette is neutral with a red primary, and `VITE_APP_NAME` falls back to “Laravel.” Domain tokens, typography hierarchy, status semantics, spoiler/restriction patterns, workspace layouts, public content layout, and immersive boundaries are absent.

## Accessibility and responsive baseline

Strengths: Radix-managed focus, visible focus styles, screen-reader text in controls, semantic breadcrumb/pagination/carousel patterns, form labels/errors, passkey/2FA ARIA, responsive sidebar/header layouts, and theme initialization.

Gaps: no documented WCAG target/test matrix, skip links, high-contrast strategy, graph/timeline alternatives, spoiler announcements, global error/offline/conflict states, or public immersive fallback. Some starter custom components use raw dark-mode colours, manual icon sizing, and direct external anchors contrary to the newer semantic component conventions. No automated accessibility or browser test tooling exists.

## Technical debt and dependency posture

- Replace `welcome.tsx`; keep auth/security logic while reskinning through semantic tokens.
- Evolve rather than rewrite app shell primitives; create distinct Public/Fan/Workspace layouts.
- Keep generated Wayfinder directories generated and out of hand-authored feature structure.
- Normalize custom components toward Field, semantic tokens, full Card composition, grouped menu items, and accessible overlay titles during their implementation prompts.
- No UI dependency is currently required to begin Prompt 13. Testing, motion, and immersive dependencies require later explicit approval and evidence.

Planning can safely proceed without modifying frontend code or application behavior.
