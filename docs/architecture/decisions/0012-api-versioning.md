# ADR 0012: API Versioning

- Status: Accepted
- Context: Web, future NativePHP Mobile, and integrations need a stable contract independent of Inertia.
- Decision: Preserve URL-versioned `/api/v1`, existing success/error envelope and request IDs. Additive fields are compatible; breaking semantics require v2. Resources, policy/spoiler filtering, cursor metadata, and idempotency keys are standard.
- Alternatives considered: unversioned API; header-only versioning; GraphQL now.
- Consequences: clear client support and parallel versions; deprecation requires an announced window once releases exist.
- Security implications: every surface declares auth, verification, permission, limits, and data minimization; token abilities only narrow access.
- Migration implications: no change to existing v1 health/me endpoints; product resources are additive.
- Future review conditions: client needs prove GraphQL or a different versioning strategy materially better.

## Prompt 8 implementation note

Owner-private User Journey resources are additive v1 fields/routes. Retry-prone progress/session writes use scoped client identifiers, stale versions retain HTTP 409, page sizes remain bounded, and no route accepts a user identifier.

Prompt 9 adds owner-only report/appeal/notification contracts and permission-scoped moderation contracts. Notification type plus schema version is the stable client contract; raw payloads/private notes remain internal. Case, appeal, and preference conflicts retain HTTP 409.

Prompt 10 adds Community resources additively: private records use non-enumerating 404 responses, feed/comment collections use bounded cursors, invitation hashes are never serialized, bookmarks/voters are private, and stale Bunker/content/poll writes use stable HTTP 409.
