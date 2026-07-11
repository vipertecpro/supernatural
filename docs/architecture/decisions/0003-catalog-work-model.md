# ADR 0003: Catalog Work Model

- Status: Accepted
- Context: TV series, films, books, comics, games, and specials share identity/publication fields but have type-specific structure.
- Decision: Use `works` as the shared bibliographic root with `work_type`; dedicated `series_details`, `seasons`, and `episodes`; releases, translations, collections, and viewing orders are separate relational tables. Type-specific fields do not accumulate in JSON.
- Alternatives considered: a table per medium; single universal contents table; JSON-only work metadata.
- Consequences: common API/search is simple while integrity remains explicit. Some types may later earn extension tables. Catalog roots archive in place; they do not use soft deletion, and durable children restrict exceptional hard deletion.
- Security implications: publication and rights checks apply at the root; child visibility cannot exceed its work.
- Migration implications: Prompt 4 builds only the catalog core and reuses `universes`.
- Future review conditions: a work type develops substantial validated fields that justify a dedicated extension.
