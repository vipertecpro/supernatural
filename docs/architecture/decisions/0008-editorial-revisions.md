# ADR 0008: Editorial Revisions

- Status: Accepted
- Context: Every meaningful edit needs attribution without cloning large relational graphs on every keystroke.
- Decision: root records hold current approved state; `editorial_revisions` store version metadata and changed-field JSON patches; `revision_blocks` store large text blocks; structured child changes use explicit revision items. Approval atomically applies a revision and records an action.
- Alternatives considered: full-row snapshots only; event sourcing; overwriting in place.
- Consequences: compact history and attributable publication; reconstruction tooling and schema-versioned patches are required.
- Security implications: drafts/review notes remain restricted; rejected or removed text is not public API output.
- Migration implications: start revisions with Catalog editorial text, then opt modules in deliberately.
- Future review conditions: patch reconstruction proves unreliable or legal retention requires immutable full snapshots.

## Prompt 5 implementation note

Implemented allowlisted structured revision items and checksum-backed plain-text `revision_blocks`; no client-controlled JSON Patch path is accepted. Immutable `editorial_actions` records decisions and transitions, while one-active-primary assignments preserve reassignment history. Application uses a row-locked transaction and after-commit scalar-ID events.

Prompt 6 opts Lore entity, translation, alias, appearance, relationship, timeline, and entry targets into the same registry and transaction. Lore/Catalog ownership is revalidated during application and lock versions increment once.
