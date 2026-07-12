# Security Policy

## Supported Versions

The project is pre-release and under active development. Security fixes are applied only to the current `main` branch until versioned releases and a formal support window exist.

## Private Reporting

Use the repository's private GitHub vulnerability-reporting or Security Advisory flow. Do not disclose a vulnerability, exploit, secret, private user information, authentication material, or sensitive infrastructure detail in a public issue or pull request.

Include:

- a concise description and affected component;
- reproducible steps using non-production data;
- observed and expected behavior;
- impact and realistic attack prerequisites;
- a minimal proof of concept when safe;
- suggested remediation, if known.

Do not test against production services, access other users' data, degrade availability, exfiltrate information, or retain secrets. Stop testing when evidence is sufficient.

## Handling Process

Maintainers will acknowledge and triage reports as capacity allows, reproduce the issue, coordinate a fix and tests, and publish an advisory when appropriate. This project does not promise a fixed response or resolution time. Reporters should keep details private until maintainers confirm coordinated disclosure is safe.

Content-rights and takedown concerns are not security vulnerabilities; follow `COPYRIGHT_AND_TAKEDOWN.md`.

## Moderation and notification data

Report identity, evidence, case notes, appeal text, notification payloads, delivery failures, and user restrictions are sensitive application data. Access must remain owner- or case-scoped and audited. Do not place report/appeal bodies, reporter identity, rendered email content, provider responses, private Journey history, playback positions, tokens, or headers in logs or public issues. Delivery retries must remain bounded and must not bypass notification preferences or mandatory safety rules.
