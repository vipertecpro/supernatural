# ADR 0010: Search Evolution

- Status: Accepted
- Context: Early scale does not justify a paid/external engine, but permission/spoiler filtering and future typo tolerance are required.
- Decision: Begin with a relational `search_documents` projection and FULLTEXT/portable fallback queries. Keep an engine-neutral indexing interface; adopt Scout plus a selected engine only at measured thresholds.
- Alternatives considered: direct per-table LIKE queries; immediate hosted search; database as permanent advanced search engine.
- Consequences: one stable result contract and rebuildable projection; initial typo tolerance is limited.
- Security implications: index only approved text and enforce visibility/spoiler filters both at indexing and query time.
- Migration implications: search documents can be reindexed from source tables and later synchronized through Scout.
- Future review conditions: p95 search over 300 ms at expected load, more than one million public documents, or required typo/facet/relevance quality unattainable in MySQL.

## Prompt 7 implementation note

Implemented portable relational token matching and deterministic title weighting over bounded candidates. SQLite/MySQL use the same correctness path; a future MySQL FULLTEXT optimization must retain it as fallback. Projections are locale-specific, source-versioned, event-refreshed, manually rebuildable, spoiler-filtered before pagination, and contain no raw source model serialization. No Scout or search vendor was added.

Prompt 8 keeps personal progress outside projections and ranking. Authenticated result metadata is joined from bounded owner scope keys at query time; guest result shapes contain no personal fields.
