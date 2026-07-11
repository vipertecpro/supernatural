# ADR 0012: API Versioning

- Status: Accepted
- Context: Web, future NativePHP Mobile, and integrations need a stable contract independent of Inertia.
- Decision: Preserve URL-versioned `/api/v1`, existing success/error envelope and request IDs. Additive fields are compatible; breaking semantics require v2. Resources, policy/spoiler filtering, cursor metadata, and idempotency keys are standard.
- Alternatives considered: unversioned API; header-only versioning; GraphQL now.
- Consequences: clear client support and parallel versions; deprecation requires an announced window once releases exist.
- Security implications: every surface declares auth, verification, permission, limits, and data minimization; token abilities only narrow access.
- Migration implications: no change to existing v1 health/me endpoints; product resources are additive.
- Future review conditions: client needs prove GraphQL or a different versioning strategy materially better.
