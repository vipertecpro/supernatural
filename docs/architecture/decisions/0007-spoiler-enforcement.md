# ADR 0007: Spoiler Enforcement

- Status: Accepted
- Context: Blurring already-returned text cannot protect search, notifications, chat, mobile, or AI output.
- Decision: Normalize earliest-safe boundaries to work/season/episode rows, keep severity and custom warning, and evaluate viewer progress server-side before query/resource serialization. Missing classification uses a conservative warning/redaction policy.
- Alternatives considered: tags; frontend blur; one global episode number; user-only self-reporting.
- Consequences: consistent decisions across clients; classification and progress queries need efficient projections.
- Security implications: administrative bypass is explicit, permission checked, and audited; moderators receive scoped unredacted review access.
- Migration implications: migrate Prompt 2 `earliest_progress` JSON into normalized boundary columns/tables once catalog IDs exist.
- Future review conditions: viewing-order complexity requires a versioned reachability projection.
