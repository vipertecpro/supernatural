# Environment Setup

## Runtime Requirements

- PHP 8.3 or newer, Composer 2, Node.js 22 and npm matching `package-lock.json`
- PDO driver for SQLite or the selected relational database
- Common Laravel extensions: Ctype, cURL, DOM/XML, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PDO, Session and Tokenizer
- Redis extension/client only when Redis cache/queue/Reverb scaling is selected
- FrankenPHP for the documented Octane runtime; no Swoole-only API is currently used

Run `composer check-platform-reqs` in the target environment before deployment.

## Local Setup

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

Laravel Herd serves the repository without running `php artisan serve`. The default `.env.example` uses SQLite-compatible Laravel defaults, database-backed cache/session/queue tables, log mail and disabled broadcasting. No user or administrator credential is seeded.

## Configuration Groups

- Application: `APP_*`, locale, maintenance and password hashing
- Database: `DB_*`
- Cache/session: `CACHE_*`, `SESSION_*`, optional Redis/Memcached
- Queue: `QUEUE_CONNECTION` and driver-specific values
- Mail: `MAIL_*`
- Sanctum/API/CORS: `SANCTUM_*`, `API_*`, `CORS_ALLOWED_ORIGINS`
- Reverb/broadcasting: `BROADCAST_CONNECTION`, server/app/origin/rate-limit variables, `VITE_REVERB_*`
- Octane/FrankenPHP: `OCTANE_SERVER`, `OCTANE_HTTPS`
- Logging/storage: `LOG_*`, `FILESYSTEM_DISK`, optional AWS variables
- Media: `MEDIA_QUARANTINE_DISK` (private disk, default `local`) and `MEDIA_MAX_UPLOAD_KILOBYTES` (default 10240)

Only configuration files may call `env()`. Application code reads `config()` so configuration caching remains safe.

## Queue, Reverb and Octane

Run `php artisan queue:work` for queued work and broadcasts. Reverb is optional; follow `realtime-foundation.md`. FrankenPHP/Octane is long-running: never retain request users or request data in singletons/statics, use worker recycling, and restart workers after code/config/environment changes.

Production process managers, TLS, backup, monitoring and rollback are deployment responsibilities and are not created by this foundation prompt.

## Testing

`phpunit.xml` uses in-memory SQLite, array cache/session/mail, sync queues and null broadcasting. Tests never require production services or real email. Run `php artisan test --compact`.

## Configuration Caching

After environment values are complete, production may run:

```bash
php artisan config:cache
php artisan route:cache
```

Rebuild caches and restart Octane/queue/Reverb workers after configuration changes. Do not cache configuration while required values are missing.

## Common Failures

- Verification redirects loop: confirm `User` implements `MustVerifyEmail` and `APP_URL` is correct.
- Browser Sanctum auth fails: align `APP_URL`, `SANCTUM_STATEFUL_DOMAINS`, CORS origins, cookies and HTTPS.
- Reverb connection fails: keep server bind values separate from public and `VITE_*` values; confirm Echo is enabled.
- Broadcasts do not arrive: confirm both Reverb and a queue worker are running for queued events.
- FrankenPHP serves stale code: restart/reload Octane workers.
- Generated route types are stale: run `php artisan wayfinder:generate --with-form --no-interaction` or rebuild through Vite.
- Search results are stale: run `php artisan search:rebuild --dry-run`, then a normal bounded rebuild; add `--prune` only when explicit derived-row deletion is intended.
- Media publication is blocked: verify moderation/processing states and the current unexpired Source rights decision for `hosting` or `embedding`; never bypass unknown rights.
