# ADR 0006: Source and Citation Model

- Status: Accepted
- Context: Citations must support records, fields, revisions, claims, and graph assertions while preserving rights.
- Decision: Extend existing sources/licenses; use `citations` with an allowlisted morph target plus optional field path, revision block, quote locator, and claim FK. `citation_sources` supports multiple sources. Rights are tri-state and independently reviewed.
- Alternatives considered: source IDs on every table; URLs in JSON; copying third-party content.
- Consequences: precise attribution and multi-source claims; morph-map governance and target validation are mandatory.
- Security implications: internal legal notes are administrator-only; unknown rights deny hosting/derivation by default.
- Migration implications: existing source/license data remains; add rights review and citation tables additively.
- Future review conditions: citation volume or field-level anchoring demands specialized claim storage.

## Prompt 5 implementation note

Implemented `citations` plus ordered `citation_sources`, with Catalog/revision morph allowlists and conservative excerpt limits. Implemented append-only `source_rights_reviews` as the canonical tri-state rights history. The API calls these records rights assessments for client clarity; no duplicate persistence concept was introduced.

Prompt 6 extends the morph allowlist to Lore roots, assertions, timelines, and revision targets. Relationship evidence remains citations; source-rights review semantics and excerpt limits are unchanged.
