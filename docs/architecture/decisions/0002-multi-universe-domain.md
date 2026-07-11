# ADR 0002: Multi-Universe Domain

- Status: Accepted
- Context: The platform hosts unrelated and shared fictional/entertainment universes without SaaS tenant isolation.
- Decision: `universes` are content roots; `franchises` group works within/across a universe; records carry explicit universe/work foreign keys where integrity needs them. Authorization is platform/content based, not tenant scoped.
- Alternatives considered: tenant-per-universe; one deployment/database per fandom; a Supernatural discriminator.
- Consequences: cross-universe discovery is possible and uniqueness is deliberately scoped. Data leakage prevention relies on publication/policy filters, not tenancy middleware.
- Security implications: private drafts remain policy protected; universe selection never grants authority.
- Migration implications: preserve existing `universes`; add franchises and scoped translations/slugs.
- Future review conditions: legally isolated operators or customer-owned deployments require genuine tenant boundaries.
