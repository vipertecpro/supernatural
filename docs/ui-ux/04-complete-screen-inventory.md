# Complete Screen Inventory

This inventory contains **206 screens**. “Ready” means the current backend/API is sufficient for the proposed screen; it does not mean the screen exists. Page routes are proposed unless identified as existing.

## Legend and per-screen state profiles

Access: `Public`; `Guest`; `A/V` authenticated and verified; or `A/V + capability/local role`. Current: `existing`, `partial`, `missing`, `deferred`. Readiness: `R` ready now, `m` minor backend gap, `M` major backend gap, `D` deferred.

Each row references a complete state profile:

- **P1 Public content:** shape-matched Skeleton; empty means no published/eligible records; request-ID error with retry; 404 for hidden/private; server spoiler visible/warning/redacted/hidden; one-column phone and context sidebar desktop; semantic landmarks, headings, skip link, keyboard source/graph alternative.
- **P2 Public/static:** stable header Skeleton only when content is dynamic; empty is omitted section; generic 404/5xx; no private data; no spoiler content unless explicitly mapped; responsive reading width; semantic document and accessible media.
- **A1 Auth:** labelled secure Form, inline Spinner, generic credential/network error, verification/rate-limit state; no spoiler data; single-column mobile; autocomplete, error summary, focus restoration, passkey/2FA alternatives.
- **O1 Owner-private:** Skeleton without count leakage; Empty with safe next action; 401/verification/403/409/429 mapping; never expose another owner; spoiler-aware returned fields; phone cards/full-screen forms; privacy label, keyboard/focus/live-region support.
- **C1 Community/Bunker:** feed/thread Skeleton; no-post/comment/member Empty; membership/restriction/block errors use privacy-safe copy; private Bunker 404; spoiler-safe posts/polls/media; single-column phone/full-screen composer; accessible reactions, polls, menus, roles.
- **W1 Workspace:** table/form Skeleton; empty queue with filters/create action; permission/case-scope/lock-conflict errors; spoiler/moderation fields shown only by capability; table-to-card phone and detail sheet desktop; captions/headers/focus/diff and dangerous-confirmation support.
- **S1 Safety:** owner/case-scoped Skeleton; empty list/status; generic direction-safe denial; no target/reporter/private-note/Journey leakage; spoiler state remains distinct; phone sheet/desktop dialog; consequence text and explicit confirmation.
- **D1 Deferred:** explanatory non-functional roadmap record only; no live CTA, route, API call, loading promise, or “coming soon” entitlement; accessible plain status.

## Public website (30)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| PW-01 Cinematic Homepage | `/` existing | Public | Explain product; Explore / Register, Open app | original hero, premise, Catalog/Lore/Journey/Community/open-source summaries; public summaries proposed from existing APIs | P2 | P15; R / partial starter replacement |
| PW-02 About Platform | `/about` | Public | Explain mission/boundaries; Explore / GitHub | editorial static content; no API | P2 | P15; R / missing |
| PW-03 Explore Universes | `/explore` | Public | Discover universes; Open universe / Search | published universes, franchises/works counts only if safely returned; public Catalog | P1 | P16; R / missing |
| PW-04 Universe Landing | `/universes/{universe}` | Public/optional auth | Orient in universe; Explore works / Lore, timelines | universe, franchises, works, related; public Catalog/Search | P1 | P16; R / missing |
| PW-05 Franchise Detail | `/franchises/{franchise}` | Public/optional auth | Understand franchise; Open work / related | franchise and published works; public Catalog | P1 | P16; R / missing |
| PW-06 Work or Series Detail | `/works/{work}` | Public/optional auth | Read safe work context; Start/continue / seasons, sources | work, translations, seasons, Media, related; public Catalog/Media/Search | P1 | P16; R / missing |
| PW-07 Season Detail | `/seasons/{season}` | Public/optional auth | Browse episodes; Open episode / work | season and episodes; public Catalog | P1 | P16; R / missing |
| PW-08 Episode Detail | `/episodes/{episode}` | Public/optional auth | Read safe episode context; Sign in/update progress / sources | episode, Media/related; public Catalog/Search | P1 | P16; R / missing |
| PW-09 Lore Directory | `/lore` | Public/optional auth | Browse entities; Filter/search / timelines | public Lore index; `/api/v1/universes/{universe}/lore` | P1 | P16; R / missing |
| PW-10 Lore Entity Detail | `/lore/{entity}` | Public/optional auth | Understand entity; Explore links / appearances/sources | entity, aliases, appearances, relationships, timeline; Lore APIs | P1 | P16; R / missing |
| PW-11 Relationship Explorer | `/lore/{entity}/relationships` | Public/optional auth | Explore graph; Select relation / structured list | bounded relationships; Lore relationship APIs | P1 | P16; R / missing |
| PW-12 Timeline Directory | `/timelines` | Public/optional auth | Browse timelines; Open timeline / filter | published universe timelines | P1 | P16; R / missing |
| PW-13 Timeline Detail | `/timelines/{timeline}` | Public/optional auth | Follow chronology; Open entry/entity / sources | timeline and entries APIs | P1 | P16; R / missing |
| PW-14 Public Search | `/search` | Public/optional auth | Find knowledge; Submit / filters | search input, safe suggestions; Search APIs | P1 | P16; R / missing |
| PW-15 Search Results | `/search?q=` | Public/optional auth | Evaluate results; Open result / refine | search documents, cursor/filter state | P1 | P16; R / missing |
| PW-16 Public Bunker Directory | `/bunkers` | Public/optional auth | Discover public Bunkers; Open / filter | categories and published public Bunkers | P1 | P18; R / missing |
| PW-17 Public Bunker Detail | `/bunkers/{bunker}` | Public/optional auth | Review identity/rules; Join/request / feed | Bunker, rules, members where authorized | C1 | P18; R / missing |
| PW-18 Global Community Feed | `/community` | Public/optional auth | Read public posts; Open post / sign in | global cursor feed | C1 | P18; R / missing |
| PW-19 Public Post Detail | `/community/posts/{post}` | Public/optional auth | Read thread; Comment/sign in / report | post, comments, polls, Media | C1 | P18; R / missing |
| PW-20 Public Viewing Orders | `/viewing-orders` | Public | Discover orders; Open / universe filter | published viewing orders | P1 | P16; R / missing |
| PW-21 Viewing Order Detail | `/viewing-orders/{order}` | Public/optional auth | Review sequence; Use in Journey / items | viewing order and items | P1 | P16; R / missing |
| PW-22 Open Source / GitHub | `/open-source` | Public | Explain contribution; View repository / governance | static project/repository facts | P2 | P15; R / missing |
| PW-23 Content Policy | `/policies/content` | Public | Explain content rules; Report / related policies | `CONTENT_POLICY.md` rendered/curated | P2 | P15; R / missing |
| PW-24 Copyright / Takedown | `/policies/copyright` | Public | Explain process; Contact approved channel / policy links | copyright/takedown document | P2 | P15; R / missing |
| PW-25 Privacy Placeholder | `/policies/privacy` | Public | State current privacy boundary; contact / policies | owner-approved legal copy required | P2 | P15; m / missing |
| PW-26 Terms Placeholder | `/policies/terms` | Public | State use terms; contact / policies | owner/legal copy required | P2 | P15; m / missing |
| PW-27 Accessibility Statement | `/accessibility` | Public | Publish tested commitments; feedback / controls | requires measured implementation status/feedback channel | P2 | P15; m / missing |
| PW-28 Not Found | fallback 404 | Public | Recover safely; Search / Home | no API; privacy-safe not-found | P2 | P13/15; R / missing |
| PW-29 Restricted Content | direct safe route | Public/authorized | Explain safe restriction; Appeal/report / return | stable error/public reason only | S1 | P13/15; R / missing |
| PW-30 Maintenance / Unavailable | error 503 | Public | Explain outage; Retry / status | health signal; approved status link/content gap | P2 | P13/15; m / missing |

## Authentication and onboarding (16)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| AU-01 Sign In | `/login` existing | Guest | Authenticate; Sign in / passkey, reset, register | Fortify session/passkey | A1 | P14; R / existing |
| AU-02 Registration | `/register` existing | Guest | Create account; Register / sign in | Fortify registration | A1 | P14; R / existing |
| AU-03 Verification Notice | `/email/verify` existing | Auth unverified | Verify; resend / sign out | Fortify verification | A1 | P14; R / existing |
| AU-04 Verification Completed | proposed post-verify | Auth | Confirm and continue; Continue / settings | verification redirect/completion destination decision | A1 | P14; m / missing |
| AU-05 Forgot Password | `/forgot-password` existing | Guest | Request reset; Send / sign in | Fortify reset link | A1 | P14; R / existing |
| AU-06 Reset Password | `/reset-password/{token}` existing | Guest | Set new password; Reset / sign in | Fortify new password | A1 | P14; R / existing |
| AU-07 Confirm Password | `/user/confirm-password` existing | Auth | Reauthenticate; Confirm / cancel | Fortify confirmation | A1 | P14; R / existing |
| AU-08 Two-Factor | `/two-factor-challenge`; settings security | Guest/Auth | Complete/manage 2FA; Verify / recovery code | Fortify 2FA/passkeys | A1 | P14; R / existing |
| AU-09 Account Suspended | proposed restriction route | Auth | Explain restriction; Appeal / notifications, sign out | restrictions/appeals/notifications; page routing gap | S1 | P14; m / missing |
| AU-10 Onboarding Intro | `/onboarding` | A/V | Explain setup; Begin / defer | proposed completion/checkpoint state | O1 | P14; m / missing |
| AU-11 Universe Interests | `/onboarding/interests` | A/V | Select interests; Continue / back | user fandom preferences; orchestration gap | O1 | P14; m / missing |
| AU-12 Viewing Progress | `/onboarding/progress` | A/V | Set safe boundary; Save / skip | progress/Journey APIs; staged orchestration gap | O1 | P14; m / missing |
| AU-13 Spoiler Tolerance | `/onboarding/spoilers` | A/V | Set tolerance; Save / explain | Journey preferences; staged orchestration gap | O1 | P14; m / missing |
| AU-14 Preferred Viewing Order | `/onboarding/order` | A/V | Choose order; Save / preview | viewing orders/Journey; staged orchestration gap | O1 | P14; m / missing |
| AU-15 Community / Privacy Defaults | `/onboarding/privacy` | A/V | Explain defaults; Save / back | partial preference APIs; consolidated defaults gap | O1 | P14; m / missing |
| AU-16 Onboarding Complete | `/onboarding/complete` | A/V | Confirm setup; Open app / review | completion flag/redirect gap | O1 | P14; m / missing |

## Fan dashboard and Journey (16)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| FA-01 Fan Home | `/app` (`/dashboard` existing placeholder) | A/V | Resume fandom work; Continue / search, notifications | continue, Journey, watchlist, memberships, notifications; aggregate prop gap | O1 | P17; m / partial |
| FA-02 My Journey | `/app/journey` | A/V owner | Manage journeys; Start / filter | `/me/journeys` | O1 | P17; R / missing |
| FA-03 Journey Detail | `/app/journey/{journey}` | A/V owner | Track one journey; Update / pause/resume/complete | Journey detail/mutations | O1 | P17; R / missing |
| FA-04 Continue Watching | `/app/continue` | A/V owner | Resume next content; Open / update | `/me/continue-watching` | O1 | P17; R / missing |
| FA-05 Viewing Progress | `/app/progress` | A/V owner | Review current progress; Update / filter | `/me/progress` | O1 | P17; R / missing |
| FA-06 Progress Correction | `/app/progress/{target}/edit` | A/V owner | Correct boundary; Save / reset safely | progress mutation/lock | O1 | P17; R / missing |
| FA-07 Viewing Sessions | `/app/sessions` | A/V owner | Review/manage sessions; Start/finish / filter | `/me/viewing-sessions` | O1 | P17; R / missing |
| FA-08 Rewatch Cycles | `/app/rewatches` | A/V owner | Manage rewatches; Start / complete/abandon | `/me/rewatches` | O1 | P17; R / missing |
| FA-09 Watchlists | `/app/watchlists` | A/V owner | Organize saved items; Create / open | `/me/watchlists` | O1 | P17; R / missing |
| FA-10 Watchlist Detail | `/app/watchlists/{watchlist}` | A/V owner | Edit ordered list; Add/reorder / remove | watchlist/items APIs | O1 | P17; R / missing |
| FA-11 Favourites | `/app/favourites` | A/V owner | Review favourites; Explore / remove | `/me/favourites` | O1 | P17; R / missing |
| FA-12 Ratings | `/app/ratings` | A/V owner | Review ratings; Update / open content | `/me/ratings` | O1 | P17; R / missing |
| FA-13 Private Notes | `/app/notes` | A/V owner | Browse notes privately; Create / open | `/me/notes` body-minimized list | O1 | P17; R / missing |
| FA-14 Note Editor | `/app/notes/{note}` | A/V owner | Edit private note; Save / delete | notes detail/mutations | O1 | P17; R / missing |
| FA-15 Preferred Viewing Orders | `/app/viewing-orders` | A/V owner | Choose defaults; Save / inspect | viewing orders + Journey preferences | O1 | P17; R / missing |
| FA-16 User Search History | `/app/search/history` | A/V owner | Review/delete history; Clear / rerun | no owner history API/privacy contract | O1 | later; M / missing |

## Catalog and Lore in fan application (15)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| CL-01 Catalog Explorer | `/app/explore` | A/V | Discover safe content; Search/open / filter | Catalog/Search | P1 | P16/17; R / missing |
| CL-02 Work Detail | `/app/works/{work}` | A/V | Read/track work; Continue / favourite/rate/note | Catalog + Journey/Media | P1/O1 | P16/17; R / missing |
| CL-03 Season Browser | `/app/seasons/{season}` | A/V | Browse/update episodes; Open / progress | seasons/episodes/progress | P1/O1 | P16/17; R / missing |
| CL-04 Episode Detail | `/app/episodes/{episode}` | A/V | Read/update episode; Mark progress / note/rate | episode + Journey | P1/O1 | P16/17; R / missing |
| CL-05 Lore Explorer | `/app/lore` | A/V | Browse Lore; Search / filter | Lore/Search | P1 | P16; R / missing |
| CL-06 Lore Entity Detail | `/app/lore/{entity}` | A/V | Understand entity; Explore relations / save | Lore/Media/Search | P1 | P16; R / missing |
| CL-07 Interactive Relationship Graph | `/app/lore/{entity}/graph` | A/V | Explore links; Select / structured list | bounded relationships | P1 | P16; R / missing |
| CL-08 Appearance History | `/app/lore/{entity}/appearances` | A/V | Trace appearances; Open record / filter | appearances | P1 | P16; R / missing |
| CL-09 Timeline Explorer | `/app/timelines` | A/V | Browse timelines; Open / filter | timelines | P1 | P16; R / missing |
| CL-10 Timeline Detail | `/app/timelines/{timeline}` | A/V | Follow entries; Open entity / sources | timeline entries | P1 | P16; R / missing |
| CL-11 Media Gallery | contextual `/media` | A/V | View eligible Media; Open / attribution | Media attachments/assets/embeds | P1 | P16; R / missing |
| CL-12 Source / Citation Drawer | contextual modal | Public/A/V authorized | Inspect evidence; Open source / copy citation | citations are embedded/management APIs; unified public read gap | P1 | P16; m / missing |
| CL-13 Spoiler Warning Dialog | contextual | Any authorized | Explain warning; Reveal / settings | spoiler Resource state | S1 | P13/16; R / missing |
| CL-14 Redacted Content State | contextual | Any authorized | Explain withheld fields; settings / return | redacted Resource | S1 | P13/16; R / missing |
| CL-15 Hidden Content State | contextual | Any | Recover without leak; return / search | omitted/404 contract | S1 | P13/16; R / missing |

## Search and discovery (7)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| SE-01 Global Search Overlay | modal/command | Public/A/V | Search anywhere; Submit / close | search/suggestions | P1 | P16; R / missing |
| SE-02 Search Results | `/search` or `/app/search` | Public/A/V | Select result; Open / refine | search cursor | P1 | P16; R / missing |
| SE-03 Search Filters | URL/sheet | Public/A/V | Narrow safely; Apply / clear | allowlisted filters | P1 | P16; R / missing |
| SE-04 Suggestions | overlay list | Public/A/V | Accelerate query; Select / submit raw | suggestions | P1 | P16; R / missing |
| SE-05 Related Content | contextual section | Public/A/V | Continue discovery; Open / see all | discovery related | P1 | P16; R / missing |
| SE-06 Empty Search | results state | Public/A/V | Recover; clear / explore | empty search response | P1 | P16; R / missing |
| SE-07 Spoiler-Filtered Search | results state | Public/A/V | Explain omissions safely; settings / continue | spoiler-filtered Search | S1 | P16; R / missing |

## Community and Bunkers (25)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| CO-01 Community Home | `/app/community` | A/V | Orient activity; Open feed / create | feeds/categories/memberships | C1 | P18; R / missing |
| CO-02 Global Feed | `/app/community/feed` | A/V | Read safe public activity; Open / create | global feed | C1 | P18; R / missing |
| CO-03 Universe Feed | `/app/universes/{universe}/community` | A/V | Read universe activity; Open / create | universe feed | C1 | P18; R / missing |
| CO-04 Post Detail | `/app/community/posts/{post}` | A/V | Read/interact; comment/react / report | post/comments/polls | C1 | P18; R / missing |
| CO-05 Create Post | `/app/community/posts/create` | A/V + restriction/member checks | Publish; submit / preview/cancel | post create, spoiler, references | C1 | P18; R / missing |
| CO-06 Edit Post | `/app/community/posts/{post}/edit` | author/local capability | Update; save / remove | post update/lock | C1 | P18; R / missing |
| CO-07 Comment Thread | post section | A/V + access | Discuss; comment/reply / react/report | comments/reactions | C1 | P18; R / missing |
| CO-08 Poll Results | post section | authorized viewer | Vote/view; vote / remove vote | poll/votes | C1 | P18; R / missing |
| CO-09 My Community Bookmarks | `/app/community/bookmarks` | A/V owner | Review saved posts; open / remove | `/me/community-bookmarks` | O1 | P18; R / missing |
| CO-10 Bunker Directory | `/app/bunkers` | A/V | Discover Bunkers; open / create | categories/Bunkers | C1 | P18; R / missing |
| CO-11 Bunker Detail | `/app/bunkers/{bunker}` | A/V + visibility | Understand/join; join / rules/feed | Bunker detail | C1 | P18; R / missing |
| CO-12 Bunker Feed | `/app/bunkers/{bunker}/feed` | A/V + member/public | Participate; create / filter | Bunker feed | C1 | P18; R / missing |
| CO-13 Create Bunker | `/app/bunkers/create` | A/V + capability | Create draft Bunker; save / cancel | Bunker create | C1 | P18; R / missing |
| CO-14 Edit Bunker | `/app/bunkers/{bunker}/edit` | local owner/admin | Update; save / archive/publish | Bunker update/lifecycle | C1 | P18; R / missing |
| CO-15 Bunker Members | `/app/bunkers/{bunker}/members` | authorized viewer | Review members; inspect role / manage if allowed | members/memberships | C1 | P18; R / missing |
| CO-16 Bunker Rules | `/app/bunkers/{bunker}/rules` | viewer; manage by local role | Read/manage rules; acknowledge/save / reorder | rules APIs | C1 | P18; R / missing |
| CO-17 Join Request | Bunker action/sheet | A/V + eligible | Request membership; submit / cancel | join request create/withdraw | C1 | P18; R / missing |
| CO-18 Join Requests Management | `/app/bunkers/{bunker}/requests` | local reviewer | Review requests; approve/reject / inspect | mutations exist; authorized request-list API missing | W1 | P18; M / missing |
| CO-19 Invitations | `/app/bunkers/invitations` | A/V owner/local inviter | Review invitations; accept/decline / revoke | mutations/notifications exist; owner list API missing | O1 | P18; M / missing |
| CO-20 Invitation Acceptance | notification/deep route | invited A/V | Review and accept; accept / decline | invitation accept/decline | C1 | P18; R / missing |
| CO-21 Bunker Role Management | members detail | owner/admin capability | Change local role; save / remove | membership update | W1 | P18; R / missing |
| CO-22 Bunker Ban Management | `/app/bunkers/{bunker}/bans` | local moderator | Review/lift bans; lift / inspect | create/lift exist; authorized list API missing | W1 | P18; M / missing |
| CO-23 Ownership Transfer | member detail/dialog | local owner | Transfer ownership; confirm / cancel | transfer API | S1 | P18; R / missing |
| CO-24 Private Bunker Not Found | generic 404 | unauthorized | Recover safely; return / public directory | no existence leak | S1 | P18; R / missing |
| CO-25 Community Restriction | contextual | restricted A/V | Explain scope; appeal / notifications | restrictions/appeals | S1 | P18; R / missing |

## Notifications (5)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| NO-01 Notifications Centre | `/app/notifications` | A/V owner | Review inbox; open / read/archive | `/me/notifications` | O1 | P17; R / missing |
| NO-02 Notification Detail | `/app/notifications/{id}` | A/V owner | Read safe notice; follow action / archive | notification detail/render | O1 | P17; R / missing |
| NO-03 Notification Preferences | `/app/settings/notifications` | A/V owner | Set channels/types; save / reset | preferences | O1 | P17; R / missing |
| NO-04 Archived Notifications | `/app/notifications/archived` | A/V owner | Review archive; open / mark unread | filtered notifications | O1 | P17; R / missing |
| NO-05 Delivery Failure State | workspace notification ops | authorized operator | Diagnose delivery; retry/inspect / return | no safe delivery-operations API | W1 | P20; M / missing |

## Interaction safety and privacy (11)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| IS-01 Blocked Users | `/app/settings/blocks` | A/V owner | Manage blocks; unblock / inspect effect | `/me/blocks` | S1 | P18; R / missing |
| IS-02 Muted Users | `/app/settings/mutes` | A/V owner | Manage mutes; edit/unmute | `/me/mutes` | S1 | P18; R / missing |
| IS-03 Block Confirmation | user action dialog | A/V | Explain effect; block / cancel/report | block create | S1 | P18; R / missing |
| IS-04 Mute Configuration | user action dialog | A/V | Select scope/expiry; mute / cancel | mute create | S1 | P18; R / missing |
| IS-05 Unblock Confirmation | settings dialog | A/V owner | Explain restored interaction; unblock / cancel | block delete | S1 | P18; R / missing |
| IS-06 Unmute Confirmation | settings dialog | A/V owner | Explain restored personalization; unmute / cancel | mute delete | S1 | P18; R / missing |
| IS-07 Privacy Settings | `/app/settings/privacy` | A/V owner | Manage privacy defaults; save / learn | canonical privacy settings persistence absent | O1 | later; M / missing |
| IS-08 Spoiler Settings | `/app/settings/spoilers` | A/V owner | Manage reveal policy; save / reset | Journey preferences | O1 | P17; R / partial appearance only |
| IS-09 Community Settings | `/app/settings/community` | A/V owner | Manage supported defaults; save / privacy | only partial preference support | O1 | P18; m / missing |
| IS-10 Account Settings | `/settings/profile` existing | A/V owner | Update profile; save / security | profile settings | O1 | P13/14; R / existing |
| IS-11 Account Deletion | profile settings dialog existing | A/V owner + password as required | Delete account; confirm / cancel | profile destroy/deletion cleanup | S1 | P14; R / existing embedded |

## Reporting and appeals (10)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| RP-01 Report Content | contextual `/reports/create` | A/V | Submit concern; continue / cancel | categories/reports | S1 | P18; R / missing |
| RP-02 Add Evidence | report flow/detail | A/V reporter owner | Add bounded evidence; submit / skip | report evidence | S1 | P18; R / missing |
| RP-03 Report Submitted | confirmation | A/V reporter | Confirm receipt; view report / return | report reference | S1 | P18; R / missing |
| RP-04 My Reports | `/app/reports` | A/V owner | Track reports; open / withdraw | `/me/reports` | O1 | P18; R / missing |
| RP-05 Report Status | `/app/reports/{report}` | A/V owner | Review public-safe status; add evidence / withdraw | report detail | O1 | P18; R / missing |
| RP-06 Submit Appeal | eligible notice/action | A/V appellant | Appeal restriction; submit / cancel | appeals create | S1 | P17/20; R / missing |
| RP-07 My Appeals | `/app/appeals` | A/V owner | Track appeals; open / withdraw | `/me/appeals` | O1 | P17/20; R / missing |
| RP-08 Appeal Detail | `/app/appeals/{appeal}` | A/V owner | Read decision/status; withdraw / return | appeal detail/decision | O1 | P17/20; R / missing |
| RP-09 Moderation Action Notice | notification/action route | affected A/V | Understand action; appeal / policies | notification + public reason | S1 | P17/20; R / missing |
| RP-10 Restriction Notice | account/context route | affected A/V | Understand scope/duration; appeal / notifications | restriction/appeal | S1 | P17/20; R / missing |

## Contributor workspace (17)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| CW-01 Contributor Home | `/workspace/contributor` | A/V + contributor capability | Prioritize work; open revision / create | revisions/media aggregate prop gap | W1 | P19; m / missing |
| CW-02 My Revisions | `/workspace/contributor/revisions` | own/all capability | Review revisions; open / create/filter | editorial revisions | W1 | P19; R / missing |
| CW-03 Revision Detail | `/workspace/contributor/revisions/{id}` | own/reviewer capability | Understand revision; edit/submit / history | revision detail | W1 | P19; R / missing |
| CW-04 Create Revision | `/workspace/contributor/revisions/create` | create capability | Start attributable revision; create / cancel | revision create | W1 | P19; R / missing |
| CW-05 Edit Revision Items | revision tab | owner in editable state | Edit fields/children; save / remove | revision items | W1 | P19; R / missing |
| CW-06 Edit Text Blocks | revision tab | owner in editable state | Edit long text; save / compare | revision blocks | W1 | P19; R / missing |
| CW-07 Add Sources | revision evidence tab | contributor capability | Create/select source; add / inspect | no general safe source-create API route | W1 | P19; M / missing |
| CW-08 Add Citations | revision evidence tab | citation capability | Attach evidence; save / remove | editorial citations | W1 | P19; R / missing |
| CW-09 Rights Requirements | revision checklist | contributor/reviewer | Understand eligibility; resolve / source | rights assessments | W1 | P19; R / missing |
| CW-10 Spoiler Classification | revision checklist | spoiler capability | Classify boundary; save / preview | spoiler boundaries | W1 | P19; R / missing |
| CW-11 Submit for Review | revision action | owner | Submit; confirm / return | revision transition | W1 | P19; R / missing |
| CW-12 Changes Requested | revision state | owner/reviewer | Resolve feedback; edit/resubmit | revision decisions/history | W1 | P19; R / missing |
| CW-13 Approved Revision | revision state | authorized | Review approval; await/apply if capable | approval | W1 | P19; R / missing |
| CW-14 Applied Revision | revision state | authorized | Review result; open target / history | application | W1 | P19; R / missing |
| CW-15 My Media Drafts | `/workspace/contributor/media` | eligible owner | Review Media lifecycle; open / submit | Media assets/embeds | W1 | P19; R / missing |
| CW-16 Submit Media | `/workspace/contributor/media/create` | Media create capability | Submit hosted/embed record; save / cancel | Media create/quarantine | W1 | P19; R / missing |
| CW-17 Media Rights Status | media detail tab | owner/reviewer | Understand readiness; resolve / sources | Media/rights/restriction | W1 | P19; R / missing |

## Moderator workspace (15)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| MO-01 Moderation Overview | `/workspace/moderation` | A/V + moderation | Prioritize workload; open queue / filters | case/appeal aggregate prop gap | W1 | P20; m / missing (stub route) |
| MO-02 Report Queue | `/workspace/moderation/reports` | report/case capability | Triage reports; open / filter | no general moderator report-list API | W1 | P20; M / missing |
| MO-03 Report Detail | `/workspace/moderation/reports/{id}` | case-scoped | Inspect report; open case / dismiss | no direct case-scoped report detail API | W1 | P20; M / missing |
| MO-04 Case Queue | `/workspace/moderation/cases` | case view capability | Manage cases; open / create/filter | moderation cases | W1 | P20; R / missing |
| MO-05 Case Detail | `/workspace/moderation/cases/{case}` | case-scoped | Investigate; assign/act / update | case detail | W1 | P20; R / missing |
| MO-06 Evidence Viewer | case detail tab | case-scoped | Review evidence; navigate / verify | case/report evidence in authorized Resource | W1 | P20; R / missing |
| MO-07 Assignment Panel | case sheet | assign capability | Assign reviewer; save / recuse | assignment API | W1 | P20; R / missing |
| MO-08 Apply Moderation Action | case action dialog | action capability | Apply attributable action; confirm / cancel | action API | W1 | P20; R / missing |
| MO-09 User Restriction Form | case action | restriction capability | Scope/duration user restriction; apply / cancel | action/restriction | W1 | P20; R / missing |
| MO-10 Content Restriction Form | case action | restriction capability | Restrict target; apply / cancel | content restriction | W1 | P20; R / missing |
| MO-11 Appeals Queue | `/workspace/moderation/appeals` | appeal review | Review appeals; open / filter | moderation appeals | W1 | P20; R / missing |
| MO-12 Appeal Detail | `/workspace/moderation/appeals/{id}` | appeal review | Decide independently; decide / return | appeal detail/decision | W1 | P20; R / missing |
| MO-13 Delivery Inspection | `/workspace/moderation/deliveries/{id}` | explicit notification ops | Diagnose notice delivery; inspect/retry | safe delivery-operations API absent | W1 | P20; M / missing |
| MO-14 Bunker Safety Review | filtered case queue | case/local target access | Review Bunker cases; open / filter | case filtering/target context minor aggregation | W1 | P20; m / missing |
| MO-15 Community Content Review | filtered case queue | case/content access | Review Community cases; open / filter | case filtering/target context minor aggregation | W1 | P20; m / missing |

## Editorial and administration workspace (28)

| ID / screen | Route | Access | Objective; primary / secondary actions | Content and API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| AD-01 Administration Overview | `/workspace/administration` | A/V + admin access | Orient operations; open section / health | multi-domain aggregate gap | W1 | P20; m / missing (stub route) |
| AD-02 Catalog Management | `/workspace/administration/catalog` | Catalog capability | Browse managed Catalog; open/create / filter | multiple Catalog APIs; aggregate gap | W1 | P20; m / missing |
| AD-03 Universe Management | `/workspace/administration/universes` | universe capability | Manage universes; create/open / archive | universe APIs | W1 | P20; R / missing |
| AD-04 Franchise Management | `/workspace/administration/franchises` | franchise capability | Manage franchises; create/open / archive | franchise APIs | W1 | P20; R / missing |
| AD-05 Work Management | `/workspace/administration/works` | work capability | Manage works; create/open / publish/archive | work APIs | W1 | P20; R / missing |
| AD-06 Season Management | `/workspace/administration/seasons` | season capability | Manage seasons; create/open / archive | season APIs | W1 | P20; R / missing |
| AD-07 Episode Management | `/workspace/administration/episodes` | episode capability | Manage episodes; create/open / archive | episode APIs | W1 | P20; R / missing |
| AD-08 Lore Management | `/workspace/administration/lore` | Lore capability | Manage entities; create/open / publish/archive | Lore APIs | W1 | P20; R / missing |
| AD-09 Relationship Management | `/workspace/administration/relationships` | Lore relation capability | Manage assertions; create/edit / publish | relationship APIs | W1 | P20; R / missing |
| AD-10 Timeline Management | `/workspace/administration/timelines` | timeline capability | Manage timelines; create/open / publish | timeline APIs | W1 | P20; R / missing |
| AD-11 Editorial Review Queue | `/workspace/administration/editorial` | review capability | Review submissions; open / assign/filter | editorial revisions | W1 | P20; R / missing |
| AD-12 Revision Review | `/workspace/administration/editorial/{id}` | review/apply capability | Review evidence/diff; decide/apply | revision APIs | W1 | P20; R / missing |
| AD-13 Source Management | `/workspace/administration/sources` | source capability | Manage sources; create/edit / inspect rights | safe general source API absent | W1 | P20; M / missing |
| AD-14 Citation Management | revision/target context | citation capability | Manage citations; add/remove / inspect | citation APIs | W1 | P20; R / missing |
| AD-15 Rights Review Queue | `/workspace/administration/rights` | rights review | Review assessments; open / filter | rights assessments | W1 | P20; R / missing |
| AD-16 Rights Assessment Detail | `/workspace/administration/rights/{id}` | rights review | Record decision; decide / source | rights detail/create | W1 | P20; R / missing |
| AD-17 Media Management | `/workspace/administration/media` | Media capability | Manage Media; open/create / filter | Media APIs | W1 | P20; R / missing |
| AD-18 Media Moderation | media detail | Media moderation | Review quarantine/rights; publish/restrict | Media lifecycle | W1 | P20; R / missing |
| AD-19 Spoiler Classification | `/workspace/administration/spoilers` | spoiler review | Review boundaries; create/update / compare | spoiler boundaries | W1 | P20; R / missing |
| AD-20 User Management | `/workspace/administration/users` | explicit user admin | Manage accounts safely; search/open / restrict | no arbitrary-user administration API | W1 | later; M / missing |
| AD-21 Role / Permission Overview | `/workspace/administration/access` | authorization admin | Inspect assignments; view / audit | no safe API; direct editing intentionally undesigned | W1 | later; M / missing |
| AD-22 Platform Restrictions | `/workspace/administration/restrictions` | restriction capability | Review restrictions; open/lift / filter | moderation restriction APIs | W1 | P20; R / missing |
| AD-23 Notification Type Registry | `/workspace/administration/notification-types` | notification type admin | Inspect stable types; view / validate | no management/read API despite permission | W1 | later; M / missing |
| AD-24 Notification Delivery Ops | `/workspace/administration/deliveries` | notification ops | Diagnose attempts; inspect/retry | no operations API | W1 | later; M / missing |
| AD-25 Audit Logs | `/workspace/administration/audit` | audit view | Investigate attributable actions; filter/open | no audit read API | W1 | later; M / missing |
| AD-26 Feature Flags | `/workspace/administration/flags` | explicit ops | Manage flags; edit / history | backend feature-flag capability absent | W1 | later; M / missing |
| AD-27 Platform Settings | `/workspace/administration/settings` | explicit ops | Manage approved settings; save / history | backend settings API absent | W1 | later; M / missing |
| AD-28 Health / Diagnostics | `/workspace/administration/health` | admin ops | Review health; refresh / inspect queue | health API exists; private diagnostics API absent | W1 | P20; m / missing |

## Deferred Messaging and Watch Rooms (11)

| ID / screen | Proposed route | Access | Objective / boundary | API | Profile | Phase; readiness / current |
| --- | --- | --- | --- | --- | --- | --- |
| DM-01 Conversation List | `/app/messages` | future participants | Deferred list only; no current CTA | none | D1 | later; D / deferred |
| DM-02 Direct Conversation | `/app/messages/{id}` | future participants + safety | Deferred direct thread | none | D1 | later; D / deferred |
| DM-03 Group Conversation | proposed | future participants | Deferred group thread | none | D1 | later; D / deferred |
| DM-04 Bunker Chat | proposed | future Bunker conversation members | Deferred; local membership is not chat authority | none | D1 | later; D / deferred |
| DM-05 Message Search | proposed | future participants | Deferred private search | none | D1 | later; D / deferred |
| DM-06 Message Requests | proposed | future participants + safety | Deferred request workflow | none | D1 | later; D / deferred |
| WR-01 Watch Room Directory | `/watch-rooms` proposed | future eligible users | Deferred room discovery | none | D1 | later; D / deferred |
| WR-02 Watch Room Lobby | proposed | future room members | Deferred lobby | none | D1 | later; D / deferred |
| WR-03 Active Watch Room | proposed | future room members | Deferred synchronized experience | none | D1 | later; D / deferred |
| WR-04 Watch Room Polls | proposed | future room members | Deferred room polls | none | D1 | later; D / deferred |
| WR-05 Watch Room History | proposed | future authorized members | Deferred history | none | D1 | later; D / deferred |

## Readiness summary

| Classification | Count | Meaning |
| --- | ---: | --- |
| Ready now (`R`) | 154 | Existing APIs/static content support implementation; screen may still be missing |
| Minor backend gap (`m`) | 23 | Small aggregation, page-routing, legal-content, or preference/orchestration decision |
| Major backend gap (`M`) | 18 | New secure operational/list/persistence API or domain capability required |
| Deferred (`D`) | 11 | Messaging and Watch Rooms; backend does not exist |
| **Total** | **206** | Complete Prompt 12 inventory |

## Prompt 14 implementation note

AU-01–09 and AU-10–16 are now implemented or integrated: branded Fortify forms, safe suspension notice, persisted introduction/interests/progress/spoilers/order/privacy/review steps, verification resume, conflict recovery, and Dashboard completion enforcement. The separate “verification completed” experience is a redirect to the saved onboarding step or completed Dashboard rather than a transient page. No public, Fan-domain, Community, or workspace screen was pulled forward.

## Prompt 15 implementation note

PW-01, PW-02, PW-22, PW-23, PW-24, and PW-27 are implemented at `/`, `/about`, `/open-source`, `/content-policy`, `/copyright-and-takedown`, and `/accessibility`. Privacy, Terms, Catalog, Lore, Timeline, Search, Community, Bunker, and viewing-order screens remain missing/deferred under their documented phases. Prompt 15 intentionally did not create routes for them.
