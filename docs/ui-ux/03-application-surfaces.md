# Application Surfaces

## Surface boundaries

| Surface | Audience | Shell | Primary jobs | Visual density | Data boundary |
| --- | --- | --- | --- | --- | --- |
| Public website | visitors, search users, prospective contributors/collaborators | `PublicMarketingLayout` or `PublicContentLayout` | understand, explore published records, search, inspect public Community, register | spacious/editorial; selectively cinematic | public Resources only; optional auth may personalize spoiler visibility without revealing private state |
| Fan application | authenticated verified fans | `FanLayout` | continue Journey, search, read, track, join, participate, manage privacy | compact, readable, search-forward | owner data plus authorized public/member data |
| Contributor workspace | authorized contributors | `WorkspaceLayout` with contributor section | draft revisions, citations, rights/spoiler evidence, eligible Media | structured forms and diffs | own revisions plus explicitly reviewable records |
| Moderation workspace | moderators and assigned reviewers | `WorkspaceLayout` with moderation section | triage, investigate, act, appeal review | dense, calm queues and timelines | permission/case-scoped; no routine Journey or private safety-list access |
| Administration workspace | administrators, editorial/rights operators | `WorkspaceLayout` with role-aware sections | manage content/governance/operations | dense tables, filters, details | explicit permission per operation; no role-derived privacy bypass |

## Boundary rules

- Public cinematic modules are lazy and never imported by Fan or workspace shells.
- Fan navigation does not contain operational queues merely because a user has a permission; a workspace switcher provides deliberate context change.
- Contributor, moderation, and administration share layout mechanics, not data assumptions or indiscriminate navigation.
- Workspace routes use breadcrumbs, filter persistence, search, table/list alternatives, and a detail sheet; dangerous changes use explicit confirmation.
- API v1 remains the stable data contract. New Inertia page routes may aggregate existing API-equivalent domain queries but must not invent capabilities.
- Wayfinder supplies every route/action function. No feature writes hardcoded application URLs.

## Current implementation truth

The repository currently renders only the Laravel welcome page, authentication pages, a placeholder dashboard, and profile/security/appearance settings. `/moderation` and `/administration` are permission-protected no-content stubs, not workspaces. All other surfaces in this blueprint are screen architecture backed to varying degrees by the existing API.

## Proposed route namespaces

- Public: `/explore`, `/universes/{universe}`, `/works/{work}`, `/lore`, `/timelines`, `/community`, `/bunkers`, `/search`, `/about`, `/open-source`.
- Fan: `/app`, `/app/journey`, `/app/explore`, `/app/community`, `/app/bunkers`, `/app/notifications`, `/app/settings/*`.
- Contributor: `/workspace/contributor/*`.
- Moderation: `/workspace/moderation/*` (the existing `/moderation` can redirect once implemented).
- Administration: `/workspace/administration/*` (the existing `/administration` can redirect once implemented).

These are proposed page routes. Existing API routes remain under `/api/v1`.
