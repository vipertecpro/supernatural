# API-to-Screen Traceability

Authoritative baseline: 243 `/api/v1` routes plus Fortify/settings web routes. The screen inventory remains authoritative for per-screen route, state profile, phase, and readiness. Proposed page routes do not imply a new backend endpoint.

## Traceability matrix

| Screens | Existing endpoint/resource | Auth / verification / capability | Mutations | Loading/error/spoiler/moderation | Readiness |
| --- | --- | --- | --- | --- | --- |
| PW-01–02, PW-22 | public summaries assembled from existing public resources; static docs | Public | none | P2; no private props | Ready; homepage aggregation is page composition |
| PW-03–08, PW-20–21; CL-01–04 | `/universes/*`, `/franchises/*`, `/works/*`, `/seasons/*`, `/episodes/*`, `/viewing-orders/*` | public reads; verified/capability for edits | admin/contributor Catalog mutations on existing routes | P1; publication, rights, spoiler, archive states | Ready |
| PW-09–13; CL-05–10 | `/universes/{universe}/lore`, `/lore/*`, `/timelines/*`, `/timeline-entries/*` | public reads; verified Lore capabilities for writes | Lore/timeline create/update/publish/archive | P1; structured alternatives; spoiler/moderation filter | Ready |
| PW-14–15; SE-01–07 | `/search`, `/search/suggestions`, `/discovery/related/{type}/{id}` | public/optional Sanctum viewer | none | P1; URL filters; hidden results omitted | Ready; FA-16 history is not supported |
| PW-16–19; CO-01–17, CO-20–21, CO-23–25 | `/bunker-categories`, `/universes/{universe}/bunkers`, `/bunkers/*`, `/community/*`, membership/invitation/action routes | public reads where eligible; verified + local role/restriction for writes | Bunker lifecycle, membership, posts/comments/reactions/polls/bookmarks | C1; private Bunker 404; block/mute/spoiler/restriction rules | Ready |
| CO-18 | approve/reject/withdraw routes exist | verified local reviewer | decisions exist | W1; requester privacy | Major gap: no authorized pending-request list |
| CO-19 | accept/decline/revoke and notifications exist | verified owner/inviter/invitee | lifecycle exists | O1/C1 | Major gap: no owner invitation list |
| CO-22 | ban create/lift routes exist | verified local moderator | create/lift | W1; private notes excluded | Major gap: no authorized ban list |
| PW-23–24, PW-28–29 | repository policy/static/error sources | Public | none | P2/S1 | Ready |
| PW-25–27, PW-30 | proposed legal/accessibility/status content | Public | none | P2 | Minor owner/legal/operational content gap |
| AU-01–03, AU-05–08 | Fortify registration/session/verification/reset/confirmation/2FA/passkeys | guest/auth as route specifies | Fortify writes | A1; generic security errors | Ready and existing |
| AU-04, AU-09–16 | verification + `/me/journey-preferences`, progress, viewing orders, restrictions/appeals | auth; verified after verification | underlying preference/progress writes | A1/O1/S1 | Minor page/orchestration/completion gap |
| FA-01 | `/me`, continue, Journey, watchlist, memberships, notifications | verified owner | low-risk linked mutations | O1 | Minor dashboard aggregation gap |
| FA-02–15 | `/me/journeys`, `/me/progress`, `/me/viewing-sessions`, `/me/rewatches`, `/me/continue-watching`, `/me/watchlists`, `/me/favourites`, `/me/ratings`, `/me/notes`, `/me/journey-preferences` | verified owner only | supported CRUD/lifecycle | O1; conflicts; private; spoiler-safe | Ready |
| FA-16 | no owner search-history endpoint | verified owner | none | O1 | Major gap; do not implement |
| CL-11 | `/media/assets`, `/media/embeds`, `/media/attachments/{targetType}/{targetId}` | public/authorized by target | none in fan view | P1; rights/moderation/spoiler | Ready |
| CL-12 | citations exist mainly in editorial contexts/resources | public/authorized | none in drawer | P1 | Minor unified public citation-read gap |
| CL-13–15 | existing spoiler visibility fields and stable errors | viewer-aware | reveal preference only where allowed | S1 | Ready |
| NO-01–04 | `/me/notifications*`, `/me/notification-preferences` | verified owner | read/unread/archive/read-all/preferences | O1; spoiler-safe rendering | Ready |
| NO-05, MO-13, AD-24 | delivery records exist internally; no operations API | explicit future capability | proposed inspect/retry | W1; provider payload private | Major gap |
| IS-01–06 | `/me/blocks`, `/me/mutes` | verified owner | create/delete, scoped/expiring mute | S1; no direction/target notification | Ready |
| IS-07 | no canonical privacy-settings persistence/API | verified owner | proposed | O1 | Major gap |
| IS-08 | `/me/journey-preferences` | verified owner | update | O1; consequence copy | Ready |
| IS-09 | partial fandom/Journey preferences only | verified owner | partial | O1 | Minor scoped-settings decision |
| IS-10–11 | existing profile/security/appearance routes and profile deletion | verified owner; password confirmation where required | update/delete/security | O1/S1 | Ready and existing/embedded |
| RP-01–05 | `/report-categories`, `/reports`, `/me/reports*` | verified reporter owner | submit/evidence/withdraw | S1/O1; anonymity | Ready |
| RP-06–10 | `/appeals`, `/me/appeals*`, notifications/restrictions | affected verified owner; appeal eligibility | submit/withdraw | S1/O1 | Ready |
| CW-01 | editorial/media lists exist | contributor capability | none on overview | W1 | Minor aggregation gap |
| CW-02–06, CW-08–14 | `/editorial/revisions*`, items, blocks, citations, assignments/reviews/decisions/application | granular editorial capabilities | full supported workflow | W1; lock conflict; private notes excluded | Ready |
| CW-07, AD-13 | Source models exist; no safe general source create/list management API in current route map | future explicit source capability | proposed CRUD | W1; rights/private notes | Major gap |
| CW-09–10, AD-14–16, AD-19 | `/editorial/rights-assessments`, `/editorial/spoiler-boundaries`, citations | contributor/reviewer capability | assess/classify/manage | W1; evidence required | Ready |
| CW-15–17, AD-17–18 | `/media/*` | Media owner/reviewer capabilities | create/update/publish/archive/attach | W1; upload/quarantine/rights/moderation separated | Ready |
| MO-01, MO-14–15 | case endpoints with target context | moderation/case scope | linked case actions | W1; Journey excluded | Minor aggregate/filter presentation gap |
| MO-02–03 | no general moderation report list/detail endpoint | future case-scoped moderation | proposed triage/open case | W1; reporter identity protected | Major gap |
| MO-04–12 | `/moderation/cases*`, actions, restriction lifts, `/moderation/appeals*` | granular moderation/appeal capability | assign/update/act/lift/decide | W1; immutable timeline/conflicts | Ready |
| AD-01–02, AD-28 | existing domain lists and `/health` | administration access plus domain permissions | linked actions | W1 | Minor aggregation/private diagnostics gap |
| AD-03–12, AD-14–19, AD-22 | Catalog, Lore, editorial, rights, Media, spoiler, moderation APIs | explicit capability per action | supported workflows | W1 | Ready |
| AD-20–21 | no arbitrary-user or safe role/permission admin API | future explicit privileged workflow | none designed | W1; audit/dual control needed | Major gap |
| AD-23 | stable notification registry is code-owned; no read/admin endpoint | notification-types capability exists | no HTTP management | W1 | Major gap |
| AD-25 | audit persistence/policy exists; no API | audit view capability | read only proposed | W1 | Major gap |
| AD-26–27 | feature flag/settings operations not implemented | future operations capability | proposed | W1 | Major gap |
| DM-01–06, WR-01–05 | no tables/models/routes/resources | future | none | D1 | Deferred |

## Readiness totals

| Ready | Minor gap | Major gap | Deferred | Total |
| ---: | ---: | ---: | ---: | ---: |
| 154 | 23 | 18 | 11 | 206 |

## API coverage rules for implementation

- A page may aggregate existing domain queries in an Inertia controller, but new semantics or broader authorization require an API/domain change explicitly scoped in that prompt.
- UI never sends unknown filters/sorts, fabricates cursor totals, exposes raw permission names, or treats a missing endpoint as a local-only working feature.
- Public and owner Resources remain separate. Moderation/admin screens receive only policy-scoped fields; Journey and Interaction Safety private data are not “helpfully” joined.
- Proposed page routes must use Wayfinder after they exist. Generated `resources/js/actions` and `resources/js/routes` are regenerated by the established build flow and never hand-edited.
