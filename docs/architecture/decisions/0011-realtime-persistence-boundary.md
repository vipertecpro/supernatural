# ADR 0011: Real-Time Persistence Boundary

- Status: Accepted
- Context: Chat, rooms, and notifications need durable history while typing, presence, and playback ticks are transient.
- Decision: Persist messages, versions, participants, read positions, moderation state, and periodic room snapshots before broadcasting. Reverb carries after-commit events. Typing, heartbeats, and high-frequency sync ticks are ephemeral and rate limited.
- Alternatives considered: WebSocket-only chat; persisting all events; broadcasting before commit.
- Consequences: reconnecting clients recover from the API; queue/Reverb outages do not lose accepted messages.
- Security implications: channel authorization repeats membership checks; payloads are minimal and spoiler filtered.
- Migration implications: messaging tables precede broadcast events; clients use idempotency keys and monotonic IDs.
- Future review conditions: extreme fan-out requires dedicated pub/sub or separate delivery workers.

Prompt 9 persists moderation and notification records before delivery. Its notification listener and mail adapter run after commit through queues. No report, restriction, appeal, notification, progress, session, or playback event is broadcast through Reverb.
