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

## Rate Limits

`API_PUBLIC_RATE_LIMIT_PER_MINUTE` controls public endpoints and defaults to 30. `API_RATE_LIMIT_PER_MINUTE` controls authenticated endpoints and defaults to 60. Authenticated limits key by user; public limits key by IP. A 429 response includes the shared error shape and framework retry headers.

## Versioning and Deprecation

Breaking response, authentication, or semantic changes require a new URL version. Additive fields may be introduced within v1; clients must ignore unknown fields. A future deprecation must be documented before removal, include a supported migration path, and retain the old version for a stated window once releases exist.

## Mobile Compatibility

The contract avoids Inertia/browser-only response semantics and is suitable for a future NativePHP Mobile client. Token issuance, device registration, push notifications, offline synchronization, refresh/revocation UX, and product endpoints are intentionally deferred until the web/domain foundation is stable.
