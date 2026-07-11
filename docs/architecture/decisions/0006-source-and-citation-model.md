# ADR 0006: Source and Citation Model

- Status: Accepted
- Context: Citations must support records, fields, revisions, claims, and graph assertions while preserving rights.
- Decision: Extend existing sources/licenses; use `citations` with an allowlisted morph target plus optional field path, revision block, quote locator, and claim FK. `citation_sources` supports multiple sources. Rights are tri-state and independently reviewed.
- Alternatives considered: source IDs on every table; URLs in JSON; copying third-party content.
- Consequences: precise attribution and multi-source claims; morph-map governance and target validation are mandatory.
- Security implications: internal legal notes are administrator-only; unknown rights deny hosting/derivation by default.
- Migration implications: existing source/license data remains; add rights review and citation tables additively.
- Future review conditions: citation volume or field-level anchoring demands specialized claim storage.
