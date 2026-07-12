# Prompt 14 Authentication and Onboarding Implementation Audit

## Scope and architecture

The diff adds the minimum workflow persistence and composes existing typed User Journey/Spoiler models and actions. No domain preference/progress table was duplicated. No public product screen, dashboard widget, Community/workspace screen, dependency, external asset, WebGL, animation library, realtime subscription, NativePHP code, or Supernatural-specific/copyrighted material was added.

## Migration evidence

- Migration: `2026_07_12_183250_create_user_onboarding_states_table.php`.
- Empty SQLite full forward and full rollback: passed.
- Populated SQLite: 2 users became 2 completed states; Prompt 14 rollback left 2 users and removed the onboarding table.
- Local MySQL: applied as batch 12 to `supernatural_db` on loopback.
- Local backfill: 2 users, 2 onboarding states, 2 completed states. Both the existing verified and unverified user were completed and therefore not blocked.
- The migration contains no destructive alteration, reset, refresh, wipe, soft delete, or domain-row mutation.

## Security, privacy, and accessibility review

- Unauthenticated and unverified access is rejected by existing middleware; no onboarding state is exposed publicly.
- User identity comes only from the authenticated request. Future steps and stale versions return 409 without mutation.
- Draft/archived Catalog records are revalidated on mutation; selectors contain no synopsis or future episode title.
- Platform suspension runs before onboarding and renders only user-visible reason/times.
- Personal values remain in typed per-universe models; workflow state contains no preferences.
- No onboarding value is written to local storage, session storage, query parameters, or Inertia shared props.
- Semantic progress, fieldsets/legends, visible labels, focusable error summary, heading focus, live step announcement, skip link, keyboard cards, responsive controls, and reduced-motion-compatible Prompt 13 tokens are present. Formal WCAG conformance is not claimed.

## Automated evidence

Focused onboarding/auth regression run: 26 tests, 163 assertions passed. Dedicated onboarding coverage includes registration state creation, existing-user fallback, verification resume, dashboard/settings/logout rules, empty-data completion, interests, hierarchical progress/idempotency, spoiler/order/privacy persistence, future/stale 409 handling, deletion, and suspension.

The full Pest suite passed with 274 tests and 1,276 assertions. PHPStan passed with zero errors. Pint formatting and test modes passed. ESLint write/check, Prettier write/check, TypeScript, and the production build passed. Composer validation, Composer audit, and npm audit passed with no vulnerability advisories. Configuration and route caches built successfully, 307 routes loaded from cache, and application caches were cleared afterwards.

## Manual review boundaries

The real local application was walked from login through the seven-step empty-Catalog path to the dashboard at 390x844. The privacy confirmation error summary received focus before correction, completion persisted, and no browser log was produced. Login and onboarding were checked at 320x568, 390x844, 768x1024, 1024x768, and 1440x900 with one primary heading, one main landmark, a skip link/current-step marker, and no horizontal overflow. The browser pass found and corrected a 320px onboarding-header overflow before the matrix was repeated successfully.

Remaining manual checks: registration and verification-notice visual passes; VoiceOver/NVDA; forced colours; 200% zoom; explicit light/dark theme passes; reduced-motion OS setting; long translated copy; touch-target measurement; visual 409 conflict review; multi-browser passkey/2FA devices; real email delivery; production rate-limit timing; and populated large-Catalog usability. Registration, verification redirect/resume, 409 handling, and throttled Fortify boundaries are covered programmatically, but that is not represented as manual browser validation.

## Remaining risks

- The implemented spoiler domain exposes three tolerance policies, not one persisted option per severity; the UI explains the five severities while storing only supported values.
- The schema has no global primary-universe or global privacy/community preference record. Progress selects an interested work explicitly and unsupported settings remain absent.
- Watched-through onboarding is intentionally capped at 100 published episodes to avoid unbounded event creation.
- The completed state has no restart flow by design.
