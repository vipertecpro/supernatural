# ADR 0007: Spoiler Enforcement

- Status: Accepted
- Context: Blurring already-returned text cannot protect search, notifications, chat, mobile, or AI output.
- Decision: Normalize earliest-safe boundaries to work/season/episode rows, keep severity and custom warning, and evaluate viewer progress server-side before query/resource serialization. Missing classification uses a conservative warning/redaction policy.
- Alternatives considered: tags; frontend blur; one global episode number; user-only self-reporting.
- Consequences: consistent decisions across clients; classification and progress queries need efficient projections.
- Security implications: administrative bypass is explicit, permission checked, and audited; moderators receive scoped unredacted review access.
- Migration implications: migrate Prompt 2 `earliest_progress` JSON into normalized boundary columns/tables once catalog IDs exist.
- Future review conditions: viewing-order complexity requires a versioned reachability projection.

## Prompt 5 implementation note

Implemented normalized work/season/episode boundaries, attributable draft/approved classification, minimal preference/progress context, and backend visible/warning/redacted/hidden decisions. Legacy severity values are explicitly mapped; richer viewing orders and sessions remain deferred.

Prompt 6 adds Lore morph aliases and target-aware relationship serialization. Hidden assertions and timeline/appearance/alias children are omitted before output, while redacted relationships never serialize the protected target identity.

Prompt 8 expands viewer knowledge to exact completed scopes, legacy positional progress, append-only completion events, and rewatch history. A normal progress reset does not make a user unknow completed content; only an explicit full spoiler-reset event changes that scope's historical basis.
