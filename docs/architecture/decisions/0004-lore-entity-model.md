# ADR 0004: Lore Entity Model

- Status: Accepted
- Context: Lore requires common identity, search, citations, spoilers, and graph traversal plus strong type-specific fields.
- Decision: Use `lore_entities` as a typed root and dedicated extension tables (`character_details`, `performer_details`, `location_details`, `artifact_details`, `organization_details`, `lore_event_details`, `concept_details`). Creatures/species use entities plus explicit taxonomy links. Named `timelines` own ordered `timeline_entries`; the parent is required even though it was accidentally omitted from the initial inventory row.
- Alternatives considered: one table per type with polymorphic graph endpoints; unstructured universal JSON; only one wide table.
- Consequences: graph endpoints have real FKs and common APIs; extension validation is required by type.
- Security implications: root publication/policy applies consistently; private extension data never leaks through generic serialization.
- Migration implications: create root before extensions and graph assertions.
- Future review conditions: new type-specific invariants justify another extension, not arbitrary metadata growth.

## Prompt 6 implementation note

Implemented the typed root, seven named extensions, translation/alias/taxonomy rows, and the corrected named-timeline parent. Types without approved extension invariants remain root plus taxonomy; entity type changes are blocked while an extension exists.
