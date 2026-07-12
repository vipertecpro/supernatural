# API Contract v1

## Base Path and Endpoints

All version-one endpoints use `/api/v1`.

| Method | Endpoint | Authentication | Purpose |
| --- | --- | --- | --- |
| GET | `/api/v1/health` | Public; rate limited | Returns only `{status: ok}` availability data |
| GET | `/api/v1/me` | Sanctum, verified email, rate limited | Returns safe identity, roles and effective permissions |
| GET | `/api/v1/universes/{universe}/franchises` | Public, rate limited | Published franchise cursor collection |
| GET | `/api/v1/franchises/{franchise}` | Public or draft-authorized | Franchise detail |
| GET | `/api/v1/universes/{universe}/works` | Public, rate limited | Published work cursor collection with allowlisted filters |
| GET | `/api/v1/works/{work}` | Public or draft-authorized | Locale-aware, spoiler-filtered work detail |
| GET | `/api/v1/works/{work}/seasons` | Public or draft-authorized | Ancestor-filtered season cursor collection |
| GET | `/api/v1/seasons/{season}/episodes` | Public or draft-authorized | Ancestor-filtered episode cursor collection |
| POST/PATCH | Catalog roots and translations | Sanctum, verified, policy, rate limited | Validated contributor/editor actions |
| POST | `.../{record}/publish` or `.../{record}/archive` | Sanctum, verified, explicit permission | Audited lifecycle transition |
| GET/POST/PATCH | `/api/v1/editorial/revisions` and nested items/blocks/actions | Sanctum, verified, explicit editorial policy | Attributable proposal and review workflow |
| GET/POST/DELETE | `/api/v1/editorial/revisions/{revision}/citations` | Sanctum, verified, revision citation policy | Normalized source evidence |
| GET/POST | `/api/v1/editorial/rights-assessments` | Sanctum, verified, rights permission | Append-only tri-state source rights |
| GET/POST/PATCH | `/api/v1/editorial/spoiler-boundaries` | Sanctum, verified, spoiler permissions | Normalized classification paths |
| GET | `/api/v1/universes/{universe}/lore`, `/api/v1/lore/{entity}` and nested Lore resources | Public, rate limited, spoiler filtered | Published entities, aliases, appearances, one-hop relationships and timeline entries |
| GET | `/api/v1/universes/{universe}/timelines`, `/api/v1/timelines/{timeline}/entries` | Public, rate limited, spoiler filtered | Published named timelines and deterministic entries |
| POST/PATCH | Lore roots, translations, aliases, appearances, relationships, timelines and entries | Sanctum, verified, policy, rate limited | Validated draft mutation and optimistic locking |
| POST | Lore `publish` or `archive` actions | Sanctum, verified, explicit permission | Evidence/spoiler-gated audited lifecycle transition |
| GET | `/api/v1/search`, `/search/suggestions`, `/discovery/related/{type}/{id}` | Public, rate limited | Projection-backed, source-resolved, spoiler-prefiltered discovery |
| GET | `/api/v1/media/assets/{asset}`, `/media/embeds/{embed}`, `/media/attachments/{type}/{id}` | Public published or explicitly draft-authorized | Rights/moderation/publication-safe Media metadata without storage paths |
| POST/PATCH | `/api/v1/media/assets`, `/media/embeds`, `/media/attachments` and lifecycle actions | Sanctum, verified, policy, rate limited | Private quarantine, allowlisted providers/targets, optimistic locking |
| GET | `/api/v1/universes/{universe}/viewing-orders`, `/viewing-orders/{order}` and items | Public, rate limited | Published non-archived Catalog viewing paths |
| GET/POST/PATCH/DELETE | `/api/v1/me/journeys`, progress, sessions, rewatches, continue watching, watchlists, favourites, ratings, notes and preferences | Sanctum, verified, owner policy, rate limited | Private User Journey state, bounded cursors, idempotency and optimistic conflicts |
| GET/POST | `/api/v1/report-categories`, `/reports`, `/me/reports` and evidence/withdrawal | Sanctum, verified, owner policy, strict report limit | Controlled private reporting without subject case access |
| GET/POST/PATCH | `/api/v1/moderation/cases`, assignments, actions and restriction lifts | Sanctum, verified, explicit moderation permission and case scope | Versioned case workflow with private-field-safe resources |
| GET/POST | `/api/v1/me/appeals`, `/api/v1/moderation/appeals` | Sanctum, verified, owner or appeal-review policy, strict appeal limit | Eligible affected-user appeals and independent decisions |
| GET/POST/PATCH | `/api/v1/me/notifications` and notification preferences | Sanctum, verified, recipient ownership | Stable type/version, spoiler-safe render, read/archive and mandatory preference rules |

The legacy unversioned `/api/user` endpoint has been removed.

## Authentication and Verification

Sanctum supports the current first-party browser session and future scoped personal-access tokens. Browser clients use stateful cookie authentication and CSRF protection. Future mobile clients may use bearer tokens only through an explicit issuance/device flow that has not yet been implemented.

Token abilities are an additional token boundary and never replace user roles, permissions, policies, account state, or email verification. Every protected v1 endpoint must declare `auth:sanctum`, `verified`, and an appropriate rate limiter.

## Successful Responses

```json
{
  "data": {},
  "meta": {
    "api_version": "v1",
    "request_id": "correlation-id"
  }
}
```

Resources may return an object or list in `data`. Pagination will use Laravel resource pagination links/meta while preserving `api_version` and `request_id`; clients must not infer pagination from list length.

## Error Responses

```json
{
  "data": null,
  "error": {
    "code": "validation_failed",
    "message": "The submitted data is invalid.",
    "details": {
      "errors": {}
    }
  },
  "meta": {
    "api_version": "v1",
    "request_id": "correlation-id"
  }
}
```

Stable error codes currently include `unauthenticated` (401), `email_unverified` (403), `forbidden` (403), `not_found` (404), `validation_failed` (422), `optimistic_lock_conflict` (409), `invalid_editorial_transition` and its specific governance variants (409), `rate_limited` (429), `http_error`, and `unexpected_error` (500). Internal exception details, paths, SQL, credentials, and configuration are never returned. Validation details are field-keyed and authorization is enforced before controller output.

Catalog and Lore update, publish, and archive requests include `expected_version`. Successful mutation increments the target version once; stale requests never overwrite current state. Revision resources expose base and current target versions. Editorial resources never serialize private reviewer, assignment, legal, or Lore editorial notes.

Lore errors add `invalid_lore_operation`, `invalid_relationship_semantics`, `duplicate_lore_relationship`, `invalid_catalog_boundary`, `cross_universe_lore_reference`, `lore_evidence_required`, and `lore_spoiler_classification_required`. Relationship resources never recursively embed edges.

Media errors add `invalid_media_operation`, `unsafe_media_file`, `unsupported_media_provider`, `media_rights_required`, `media_moderation_required`, `media_processing_required`, `media_takedown_blocked`, `invalid_media_target`, `invalid_media_source`, `duplicate_media_attachment`, and `cross_universe_media_attachment`. Search validation rejects unknown filters/sorts and oversized/short queries before query execution.

User Journey errors add `invalid_journey_operation`, `invalid_journey_target`, `journey_target_unavailable`, `active_journey_exists`, `active_rewatch_exists`, `progress_moved_backwards`, `invalid_progress_position`, `invalid_progress_percentage`, `duplicate_watchlist_item`, and cross-universe variants. Stale writes retain `optimistic_lock_conflict`.

Moderation/notification errors add `unsupported_report_target`, `report_target_inaccessible`, `invalid_case_transition`, `case_resolution_required`, assignment/reviewer conflict and authority codes, restriction scope/authority codes, appeal eligibility/window/duplicate codes, `platform_access_restricted`, `capability_restricted`, notification type/payload/preference/retry codes, and existing `optimistic_lock_conflict`. Private explanations, reporter identity, reviewer notes, raw notification payloads, and delivery-provider bodies are never serialized to affected users.

## Rate Limits

`API_PUBLIC_RATE_LIMIT_PER_MINUTE` controls public endpoints and defaults to 30. `API_RATE_LIMIT_PER_MINUTE` controls authenticated endpoints and defaults to 60. Authenticated limits key by user; public limits key by IP. A 429 response includes the shared error shape and framework retry headers.

## Versioning and Deprecation

Breaking response, authentication, or semantic changes require a new URL version. Additive fields may be introduced within v1; clients must ignore unknown fields. A future deprecation must be documented before removal, include a supported migration path, and retain the old version for a stated window once releases exist.

## Mobile Compatibility

The contract avoids Inertia/browser-only response semantics and is suitable for a future NativePHP Mobile client. Token issuance, device registration, push notifications, offline synchronization, refresh/revocation UX, and product endpoints are intentionally deferred until the web/domain foundation is stable.

## Prompt 10 Community contract

Community reads expose published public records or authenticated active-member records. Verified writes use policies, local-role checks, central restrictions, enum Form Requests, named limits, and transactions. Private Bunkers are non-enumerating, bookmarks are owner-only, poll voters are never serialized, and no endpoint exposes another user's private Community state.

## Prompt 11 Interaction Safety contract

Verified owners may list/create/delete only their `/me/blocks` and `/me/mutes`. Resources expose the selected target and the owner's own bounded reason/scope/expiry only. Duplicate creates are idempotent, lists are cursor-bounded, request IDs remain present, and no target-facing state/direction endpoint exists.

## Prompt 14 web onboarding contract

Prompt 14 introduces no API v1 endpoint. Authenticated verified Inertia routes persist workflow checkpoints and call existing Journey domain actions. Stale or future-step web writes return HTTP 409 with a conflict page; validation remains 422 redirect/error-bag behavior. Onboarding records and values are not shared with unauthenticated clients, API tokens, or public Resources.
