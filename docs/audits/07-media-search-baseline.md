# Prompt 7 Media and Search Baseline

Date: 2026-07-12 (Asia/Kolkata)

## Repository and preservation

- Branch: `main`.
- Commit: `d176086962c588cb88280bce314555fd943834b9` (`Add optimistic locking and spoiler visibility handling`).
- Prompt 5 and Prompt 6 are committed together in that commit despite its narrow subject; the commit contains both governance migrations and the 19-table Lore implementation.
- Initial worktree: clean. Initial `git diff --check`: passed.
- No Prompt 7 file existed and no user change required preservation.

## Runtime and database safety

- Laravel 13.19.0 on PHP 8.4.23 in `local`; MySQL is selected on loopback `127.0.0.1`.
- All migrations through `2026_07_12_054029_create_lore_knowledge_graph_tables` were applied; none was pending.
- Cache, queue, and session use the database. Broadcasting uses Reverb and Octane uses FrankenPHP.
- Storage defines private `local`, public `public`, and unconfigured `s3` disks; `public/storage` was not linked.
- Prompt 7 can proceed additively. The developer MySQL database must not be reset, refreshed, wiped, or used for rollback testing.

## Approved boundary

The canonical inventory selects five Media tables (`media_assets`, `media_variants`, `external_embeds`, `media_attachments`, `media_processing_jobs`) and four Search tables (`search_documents`, `search_suggestions`, `trending_snapshots`, `search_queries`). Catalog and Lore remain authoritative. Search rows are derived and rebuildable. Hosted upload admission is private quarantine only; no remote download, rehosting, transcoding, provider fetch, or third-party thumbnail extraction is approved.
