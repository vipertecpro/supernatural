# ADR 0001: Modular Monolith

- Status: Accepted
- Context: Seventeen domains share identity, catalog, spoiler, rights, and moderation invariants; the repository is one Laravel deployment with no measured independent scaling need.
- Decision: Use a domain-organized Laravel modular monolith with one database, explicit module interfaces, after-commit events, and module-owned writes.
- Alternatives considered: conventional layer-only MVC (weak ownership); microservices (operational and consistency cost); package-per-module (premature ceremony).
- Consequences: atomic cross-table workflows and simple deployment; discipline and architecture tests must prevent boundary erosion.
- Security implications: one policy/gate system and audit trail; a compromise still has a broad process boundary, so least-privilege code paths remain required.
- Migration implications: evolve incrementally inside the existing app; no data/service split now.
- Future review conditions: sustained team ownership conflicts, independently scaling workloads, or isolation requirements proven by production metrics.
