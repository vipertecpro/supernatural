# Real-Time Foundation

## Architecture

Laravel Reverb is the optional WebSocket transport. Laravel broadcasting authorization remains the security authority, the database remains the future source of truth, and queued broadcasts require a queue worker. No chat, presence, typing, message, attachment, or notification product flow is implemented yet.

## Current Security Boundary

- `/broadcasting/auth` uses the web session stack plus `auth:sanctum` and `verified`.
- Private channels reject guests and unverified users before channel callbacks.
- `App.Models.User.{id}` additionally verifies ownership and verification state.
- Reverb origins come from `REVERB_ALLOWED_ORIGINS`; production must list exact HTTPS origins and must never use `*`.
- Client events default to `none` and Reverb application rate limiting defaults on.
- Broadcasting defaults to `null`; the React Echo singleton initializes only when `VITE_REVERB_ENABLED=true`.
- App IDs, keys and secrets come only from environment-backed configuration.

## Local Setup

Generate local-only Reverb application credentials, set `BROADCAST_CONNECTION=reverb`, configure the `REVERB_*` and matching `VITE_REVERB_*` values, set `VITE_REVERB_ENABLED=true`, then run the normal frontend development process. Start long-running services separately:

```bash
php artisan queue:work
php artisan reverb:start
```

Do not commit credentials. The application operates normally with broadcasting disabled.

## Production Operations

Run queue and Reverb processes under a non-root process supervisor with automatic restart, bounded workers, centralized logs and health monitoring. Deployments that change code/config must restart Reverb and recycle queue/Octane workers. Terminate TLS at a documented proxy or Reverb endpoint, set exact public host/port/scheme values, and restrict origins.

Scaling uses the configured Redis connection only when explicitly enabled. Capacity, connection limits, message limits, retry behavior, observability, multi-region operation and failure recovery must be tested before community launch.

## Deferred Until Chat

Conversation/message persistence, memberships, presence payloads, receipts, typing, message moderation, retention, encryption decisions and client reconnection UX remain deferred until Messaging. Prompt 11 implements blocks/mutes and their reusable evaluator without Reverb. Prompt 9 implements durable moderation and notification routing without Reverb: personal/moderation notifications recover through API v1, and progress/session/playback activity is never broadcast.

Prompt 10 is database/API only. Its scalar events dispatch after commit for notifications and future consumers, but none implements `ShouldBroadcast`; there are no Community channels, chat, presence, typing, receipts, or live-feed payloads.

Prompt 11 adds scalar after-commit block/mute lifecycle events but no `ShouldBroadcast`, channel, presence, typing, receipt, conversation, or message behavior. Future realtime authorization must call the same interaction evaluator before exposing activity.
