# Implementation Sequence and Traceability

## Ordered phases

| Phase        | Scope / tables                                                                               | Models/enums/policies/API                                                                                       | Required tests and exit criteria                                                                                                                        |
| ------------ | -------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 0            | Commit/review Prompt 2; apply its three migrations in an authorized environment              | existing foundation                                                                                             | clean reviewed baseline; schema/tests match; owner separately chooses license when ready                                                                |
| 1 (Prompt 4) | Catalog core: franchises, works, translations, series details, seasons, episodes             | Work/Season/Episode/Franchise; work/status/type/canon enums; policies; public/contributor/reviewer v1 resources | migration rollback on clean test DB, FK/unique/type validation, policy matrix, publication/spoiler/source hooks, API envelope/pagination; no UI/content |
| 2            | Editorial revisions, rights reviews, citations and normalized spoiler boundaries for Catalog | revision/rights actions and policies                                                                            | attributable approval, unknown-rights denial, backend redaction, concurrency tests                                                                      |
| 3            | Lore roots/extensions, aliases, appearances, graph and timelines                             | lore/relationship policies/resources                                                                            | endpoint-rule integrity, traversal bounds, citations and disputed/private filtering                                                                     |
| 4            | Media quarantine/assets/embeds/attachments and relational search projection                  | media/search interfaces/jobs                                                                                    | malicious upload/embed cases, rights/takedown hide, reindex reconciliation                                                                              |
| 5            | User Journey                                                                                 | progress/session/watchlist/rating/preferences                                                                   | idempotent/offline conflict, privacy, multi-work spoiler decisions                                                                                      |
| 6            | Moderation minimum viable workflow and stable notifications                                  | reports/cases/actions/restrictions/appeals; notifications/deliveries                                            | case-scoped private access, appeal separation, spoiler-safe multi-channel payload                                                                       |
| 7            | Community and Bunkers                                                                        | post/comment/poll/membership policies/resources                                                                 | feed cursor consistency, membership roles, spam/report/spoiler/media gates                                                                              |
| 8            | Persistent messaging                                                                         | conversations/messages/versions/read positions                                                                  | membership/block/idempotency/order/delete/report tests; reconnect via API                                                                               |
| 9            | Watch rooms                                                                                  | room/session/snapshot/reaction/poll tables                                                                      | sequence/channel authorization, reconnect, provider-link/no-hosting boundary                                                                            |
| 10           | Case boards and theories                                                                     | board/node/layout/connection/revision tables                                                                    | collaborator/locking/forking, theory-vs-canon and citation tests                                                                                        |
| 11           | Quizzes/gamification                                                                         | quiz/attempt/achievement/XP/challenge tables                                                                    | immutable scoring, idempotent awards, spoiler handling, anti-cheat                                                                                      |
| 12           | Events                                                                                       | event/venue/schedule/attendance/itinerary/meetup tables                                                         | official/community/meetup provenance, privacy, external-ticket boundary                                                                                 |
| 13           | Mobile hardening and scale                                                                   | device/token/push/offline contracts; optional Scout decision                                                    | NativePHP contract tests, load metrics, retention/operations readiness                                                                                  |

Seed data is limited to code-owned reference enums/taxonomies and rights-safe synthetic fixtures. No copyrighted names, summaries, quotes, images, or media. Every phase includes factories, Pest tests, policies, API Resources, audit coverage, reversible migrations, and documented rollback/data-backfill concerns. Community cannot precede moderation; messaging cannot precede blocks/reports; watch rooms cannot precede catalog and messaging.

## Traceability matrix

| Capability             | Owner         | Core tables                                    | Policies                    | API / events/jobs                              | Phase |
| ---------------------- | ------------- | ---------------------------------------------- | --------------------------- | ---------------------------------------------- | ----- |
| multi-universe catalog | Catalog       | universes, works, seasons, episodes            | Universe/Work/Episode       | `/universes`, `/works`; `WorkPublished`        | 1     |
| sources/publication    | Editorial     | sources, citations, revisions, actions         | Source/Revision             | `/sources`, `/editorial`; `ContentPublished`   | 2     |
| spoiler safety         | Spoilers      | constraints, boundaries, preferences/progress  | target policy + bypass gate | all Resources; `SpoilerClassificationChanged`  | 2/5   |
| lore graph/timeline    | Lore          | lore_entities, relationships, timeline_entries | LoreEntity/Relationship     | `/lore`; `LoreEntityPublished`                 | 3     |
| search/media           | Search/Media  | search_documents, media_assets, embeds         | target/Media                | `/search`; index/process jobs                  | 4     |
| personal journey       | User Journey  | viewing_progress, watchlists, ratings          | owner/privacy               | `/journey`; `ViewingProgressUpdated`           | 5     |
| safety/appeals         | Moderation    | reports, cases, actions, appeals               | Report/Case/Appeal          | `/reports`, `/moderation`; `ContentRestricted` | 6     |
| notifications          | Notifications | notifications, deliveries, preferences         | recipient                   | `/notifications`; delivery jobs                | 6     |
| community/groups       | Community     | posts, comments, bunkers, memberships          | Post/Comment/Bunker         | `/community`, `/bunkers`; `PostPublished`      | 7     |
| chat/presence          | Messaging     | conversations, messages, versions, receipts    | Conversation/Message        | `/conversations`; `MessageSent`                | 8     |
| synchronized rooms     | Watch Rooms   | watch_rooms, sessions, snapshots               | WatchRoom                   | `/watch-rooms`; `RoomStateChanged`             | 9     |
| theories/boards        | Case Boards   | boards, nodes, connections, revisions          | CaseBoard                   | `/case-boards`; `BoardPublished`               | 10    |
| quizzes/achievements   | Gamification  | quizzes, attempts, achievements, XP ledger     | Quiz/Attempt                | `/quizzes`; scoring/award handlers             | 11    |
| events/conventions     | Events        | events, schedules, attendance, meetups         | Event/Meetup                | `/events`; `EventPublished`                    | 12    |

## Exact Prompt 4 objective

> Implement only the fandom-neutral Catalog Core on top of the reviewed Prompt 2 foundation: add franchises, works, localized work text, series details, seasons, and episodes with integer foreign keys, explicit enums, publication/archival fields, source/citation and spoiler extension points, factories, policies, actions/Form Requests/API v1 Resources and routes, and focused Pest coverage. Do not add lore, community, chat, media uploads, search engine integration, copyrighted content, immersive UI, or mobile implementation. Verify migrations and rollback only against the test database, run the required focused/full quality gates, and preserve all unrelated working-tree changes.

Prompt 4 must not begin until Prompt 2 and Prompt 3 are reviewed/committed or otherwise safely preserved and the pending Prompt 2 migrations are intentionally applied in the target development environment.

## Prompt 4 completion note

The uncommitted Prompt 1–3 tree was explicitly preserved, the three Prompt 2 migrations were inspected and applied to the verified local database, and the six-table bounded Catalog Core was implemented without beginning Phase 2 editorial work. Prompt 5 should implement attributable editorial revisions, source/citation minimums, rights decisions, and normalized spoiler boundaries for these Catalog identifiers.

## Prompt 5 completion note

Phase 2 is implemented for Catalog targets only: attributable revisions and decisions, normalized evidence/rights/spoiler records, optimistic locking, minimal viewer context, backend redaction, API routes, audit records, after-commit events, factories, and tests. Prompt 6 should begin the next approved architecture phase only after this governance diff is reviewed; it must reuse these evidence and spoiler interfaces rather than duplicate them.

## Prompt 6 completion note

Phase 3 is implemented as the 19-table relational Lore foundation, reusing Prompt 5 revisions, citations, rights, spoiler decisions, optimistic locking, audit, and permissions. The missing named `timelines` inventory root was corrected without broad redesign. Prompt 7 may begin only the next approved phase after this diff is reviewed; it must not duplicate Lore evidence, search, or media infrastructure.

## Prompt 7 completion note

Phase 4 is implemented as the rights-aware private-quarantine Media foundation and nine-table relational Media/Search slice. Search consumes committed Catalog/Lore facts into rebuildable locale projections and enforces source publication and spoiler decisions before pagination. Prompt 8 may begin only the next approved User Journey phase after review; it must reuse the current minimal progress/spoiler contract and must not begin Community or other later phases.
