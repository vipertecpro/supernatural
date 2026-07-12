# Forms and Application States

## Form architecture

All forms use visible labels, descriptions for consequence/context, server field-error mapping, a focusable form-error summary, correct autocomplete/inputmode, and keyboard-complete controls. Placeholder text is optional example text, never a label. Inertia `<Form>` is preferred for conventional mutations; `useForm` supports multi-step/local state; `useHttp` is reserved for standalone API interactions such as search or inline owner actions.

| Form family | Specific UX contract |
| --- | --- |
| Authentication | password-manager/autocomplete safe, generic credential errors, passkey/2FA alternatives, recovery guidance |
| Onboarding | one decision group per step, back/skip rules, save checkpoint clearly marked as proposed |
| Progress | current boundary shown, quick update plus correction path, conflict recovery, spoiler consequence preview |
| Bunker | visibility consequences, rules before membership action, local role labels, lock conflict handling |
| Post/comment/poll | context and audience fixed visibly, spoiler declaration, mention/reference validation, local preview, preserve unsent body |
| Report/appeal | confidentiality statement, bounded evidence, no promise of outcome, confirmation reference |
| Revision/source/citation | staged evidence checklist, autosave only when backend supports it, diff/conflict comparison |
| Rights | permitted-use explanation before submit, tri-state decision clarity, private legal note separation |
| Moderation | target/scope/duration/effects summary, typed confirmation for severe irreversible actions, public/private reasons distinct |
| Settings | horizontal Field pattern where space allows, immediate vs Save semantics consistent, privacy consequence text |

## Drafts and conflicts

Complex unsaved forms warn before navigation and preserve local text in memory/session storage when privacy permits. Persistent server drafts are shown only for APIs that actually store draft state; Community post drafts and onboarding checkpoints are backend gaps. Optimistic-lock conflicts offer: review latest server state, compare entered changes, copy/export safe text, retry against new version, or discard. Never silently overwrite.

Uploads show client transfer, quarantine, processing, moderation, rights review, ready, failed, and restricted as separate states. A completed upload is not represented as publishable until the backend says so.

## Loading patterns

- Page Skeleton matches the final hierarchy and keeps the heading/landmark stable.
- Card/list/table Skeletons preserve dimensions and avoid layout shift.
- Inline Spinner keeps the action label (“Saving…”), disables duplicate submission, and does not block unrelated reading.
- Background refresh uses a subtle labelled indicator; existing data remains readable.
- Upload Progress exposes numeric progress when known and an indeterminate state otherwise.

## Empty-state matrix

| Empty state | Primary next action |
| --- | --- |
| No active Journey | Start a Journey |
| Empty watchlist/favourites/notes | Explore public Catalog |
| No joined Bunkers | Browse public Bunkers |
| No notifications | Continue exploring; no artificial CTA urgency |
| No reports/appeals/revisions | Explain purpose and offer eligible create action |
| No search results | Clear filters, check spelling, explore categories |
| No comments | Add the first comment when authorized |
| No moderation cases | Clear queue status; no create-case shortcut unless policy allows |

## Error mapping

| API state | UI | Recovery |
| --- | --- | --- |
| Validation 422 | field errors + summary | correct fields; preserve input |
| Unauthenticated 401 | sign-in interstitial | sign in, return to intended route |
| Unverified 403 | verification-required state | resend verification, refresh |
| Forbidden 403 | permission-safe state | return; workspace switch if relevant |
| Not found 404 | branded generic not found | safe parent/search; no private inference |
| Conflict 409 | ConflictResolver | compare/reload/retry |
| Rate limited 429 | wait guidance using retry metadata when safe | retry after interval; preserve input |
| Network/offline | OfflineBanner + unsent state | retry; never display success |
| Server 5xx | request-ID-aware ErrorState | retry/support; no internals |
| Service unavailable | maintenance state | retry/status information if approved |

## Offline/unstable network

The web app is not offline-first. It detects failed writes, preserves unsent non-sensitive form content where practical, marks stale data, allows retry, and never queues a mutation invisibly. Sensitive report, appeal, rights, moderation, block/mute, or account-deletion payloads are not persisted to browser storage by default. NativePHP offline synchronization remains deferred.

## Prompt 14 implemented forms

Auth forms now expose focusable error summaries and accessible success status. Onboarding uses server checkpoints, visible labels, fieldsets/legends, predictable Back/Continue actions, explicit optional-step copy, disabled duplicate submissions, and a dedicated 409 `ConflictState`. Form values remain in DOM/component memory only and are never copied to local/session storage. The empty universe/order states submit safe empty values without fabricating records.
