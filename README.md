# Supernatural Fandom Platform

An independently developed, source-available foundation for building fandom communities across television, film, games, anime, books, and other fictional universes. **Supernatural** is intended to be the first thematic implementation, but the application architecture and public experience are fandom-neutral.

This repository is under active development. Current functionality includes the original public homepage and trust/information pages, secure authentication, persisted onboarding, first-party roles and permissions, governed Catalog/Lore/Media/Search/Journey/Moderation/Community backend foundations, an API v1 contract, and hardened broadcasting configuration. Cinematic public Catalog/Lore/Search interfaces, complete Fan and operational workspaces, Messaging, Watch Rooms, and mobile remain future phases.

## Unofficial Project and Content Rights

This is an unofficial fan and open-source software project. It is not affiliated with, endorsed by, or sponsored by Warner Bros., The CW, the series creators, cast members, or any other rights holder.

Do not commit copyrighted episodes, music, transcripts, images, logos, fonts, video, or other protected assets without documented permission. Source code availability does not grant rights to third-party names, stories, characters, brands, or media. See [CONTENT_POLICY.md](CONTENT_POLICY.md) and [COPYRIGHT_AND_TAKEDOWN.md](COPYRIGHT_AND_TAKEDOWN.md).

## Technology

- PHP 8.3+ and Laravel 13
- Inertia 3, React 19, TypeScript, and Tailwind CSS 4
- Fortify, Sanctum, Reverb, Octane, FrankenPHP, and Wayfinder
- Pest 4, Larastan/PHPStan, Pint, ESLint, Prettier, and Vite

## Local Setup

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

Laravel Herd serves this checkout at `https://supernatural.test`. Other environments may use the repository's normal Laravel/Vite development command:

```bash
composer run dev
```

Public canonical and repository metadata are opt-in. Configure `PUBLIC_SITE_URL` with an intentional public HTTPS origin and `PUBLIC_REPOSITORY_URL` with a credential-free HTTPS GitHub, GitLab, or Codeberg repository. Missing or unsafe values are omitted from public metadata and links.

The default environment leaves broadcasting disabled. Reverb requires explicit local credentials and configuration; see [docs/project/environment-setup.md](docs/project/environment-setup.md) and [docs/project/realtime-foundation.md](docs/project/realtime-foundation.md).

No administrator account or default login credential is seeded. `php artisan db:seed` creates only idempotent role and permission definitions.

## Quality Checks

```bash
composer validate --strict
composer audit --no-interaction
vendor/bin/pint --test --format agent
composer run types:check
php artisan test --compact
npm audit --omit=dev
npm run lint:check
npm run format:check
npm run types:check
npm run build
```

## Architecture Boundaries

- Supernatural-specific content must remain data/configuration, not shared-code assumptions.
- Public, fan, administration, and future mobile experiences have separate performance and security boundaries.
- Authorization is enforced by backend gates/policies; frontend visibility is never an access control.
- Reverb transports real-time events but does not replace persistent domain data or authorization.
- Sources, licenses, attribution, and spoilers are part of the content foundation, not optional cleanup.

See [docs/project/foundation-architecture.md](docs/project/foundation-architecture.md) and [docs/project/api-contract-v1.md](docs/project/api-contract-v1.md).

## Contributing and Security

Read [CONTRIBUTING.md](CONTRIBUTING.md), [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md), and [SECURITY.md](SECURITY.md) before opening a change. Report vulnerabilities privately through GitHub's security reporting flow, never in a public issue.

## Software License Status

No standalone software license has been approved for this repository. Although a legacy package manifest currently contains an `MIT` metadata value, no `LICENSE` file exists and that metadata is not treated as an approved project-wide licensing decision. Source availability alone does not grant permission to copy, modify, or redistribute the software. The maintainers must resolve licensing before representing the repository as reusable open-source software.
## Immersive experience

Prompt 15B adds an adaptive cinematic public runtime with a lazy original WebGL scene, GSAP/Lenis choreography, reduced and silent modes, opt-in procedural audio, self-hosted open fonts, rights-gated TMDB metadata, and click-to-load official YouTube embeds. See `docs/implementation/15b-immersive-experience-overhaul.md`. Prompt 16 public knowledge-domain screens are not included.
