# ADR 0005: Knowledge-Graph Relationships

- Status: Accepted
- Context: Directed, inverse, disputed, temporal, cited relationships must be traversable without a graph database.
- Decision: `relationship_types` define direction, inverse label, symmetry, and allowed endpoint types; `lore_relationships` store source/target entity FKs and review/canon/spoiler state; citations support each assertion.
- Alternatives considered: polymorphic endpoints; free-text edges; external graph database.
- Consequences: indexed adjacency queries are relational and enforceable; complex multi-hop traversal is bounded in application queries.
- Security implications: only approved assertions become public; disputed claims retain review evidence.
- Migration implications: seed code-owned relationship definitions, create composite source/target indexes, and backfill inverse semantics without duplicate edges.
- Future review conditions: measured recursive-query latency or graph analytics exceed relational thresholds.

## Prompt 6 implementation note

Implemented controlled relationship definitions/rules and real-FK assertions. Symmetric edges use lower-ID canonical order, directed edges retain order, inverse text is not duplicated, active duplicates are rejected, and public API traversal is one-hop/cursor-bounded. Existing citations and spoiler constraints gate publication; no graph database or duplicate evidence table was added.
