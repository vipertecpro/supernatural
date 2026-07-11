# Contributing

Thank you for helping build a reusable, safe fandom-community platform.

## Before Starting

1. Read the README, code of conduct, content policy, security policy, and foundation architecture.
2. Open or reference an issue for material changes so scope and content-rights implications are visible.
3. Keep shared architecture fandom-neutral. Do not hardcode Supernatural-specific facts into reusable models, authorization, APIs, or services.
4. Never add credentials, real user data, copyrighted media, copied transcripts, downloaded episodes/music, or assets without documented rights.

## Development

Use PHP 8.3+, Composer 2, Node 22, and the lock files committed to the repository. Follow the setup in `README.md` and configure only local/test services.

Keep changes focused and follow existing Laravel, Inertia, TypeScript, and Tailwind conventions. Use first-party authorization gates and policies; never rely on hidden frontend links for security. Add migrations, factories, policies, and Pest tests for persistent behavior.

## Required Checks

Run the complete check set documented in `README.md`. Pull requests should not use formatter commands as a substitute for committing formatted code, suppress static-analysis errors, reduce test coverage, or silently ignore audit failures.

## Pull Requests

Include:

- the problem and intended outcome;
- affected architecture/security/content-rights boundaries;
- migrations and rollback behavior;
- tests added or updated;
- exact validation commands and results;
- screenshots only when UI changed and the assets are safe to publish;
- linked issue or decision record when applicable.

Do not combine unrelated cleanup, dependency upgrades, and product work. Maintainers may request changes to protect accessibility, privacy, security, performance, or content rights.

## License Notice

The software-license decision is unresolved. Contribution acceptance does not, by itself, establish a license for the repository or third-party content. Contributors must have the right to submit their work and must not include material whose redistribution they cannot authorize.
