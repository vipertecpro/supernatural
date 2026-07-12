# ADR 0013: User Journey Lifecycle and Historical Knowledge

- Status: Accepted
- Context: The preliminary User Journey inventory reserved saved theories, generic activity, and recaps but did not identify an active selected viewing order, append-only progress correction history, or rewatch root required by the approved Phase 5 behavior.
- Decision: Keep viewing orders/items Catalog-owned. Keep 12 User Journey tables by replacing the three unimplemented reservations with `user_viewing_journeys`, `viewing_progress_events`, and `rewatch_cycles`. Current `viewing_progress` is authoritative; events are append-only history; rewatches use separate cycle scopes; personal data is owner-private.
- Alternatives considered: add three tables and increase the inventory; overload viewing sessions as journeys/rewatches; use events as current state; retain generic activity payloads.
- Consequences: explicit lifecycle and knowledge semantics, deterministic continue watching, safe corrections, and no event-sourced read path. Saved theories/recaps need a later approved schema if pursued.
- Security implications: no public personal route, no user ID input, no private state in Search projections, and no device fingerprint collection.
- Migration implications: preserve both existing tables, backfill deterministic legacy scope keys, and refuse lossy rollback when richer scopes cannot fit the old unique constraint.
- Future review conditions: approved public profile projections, an export endpoint, production retention/legal hold, or measured need for a dedicated continue-watching projection.

Prompt 9 consumes only `ViewingJourneyCompleted` and `RewatchCycleCompleted` for owner notifications. It does not consume progress/session events, query private history, copy Journey data into cases, or broadcast personal activity.
