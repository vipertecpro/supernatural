# ADR 0009: Media Storage and Embeds

- Status: Accepted
- Context: Original/user media and third-party embeds have different rights and processing rules.
- Decision: `media_assets` represent hosted files; `external_embeds` represent provider-authorized URLs/IDs. Media is private until validated, rights-reviewed, processed, and moderated. Derivatives are first-class rows.
- Alternatives considered: URL columns on domain records; automatic remote download; public filesystem by default.
- Consequences: clear ownership and takedown; async processing and signed access are needed.
- Security implications: MIME sniffing, size/dimension limits, checksum, malware review where available, no SVG/active documents by default, and no arbitrary embed HTML.
- Migration implications: storage/provider choice remains configurable; DB schema does not depend on a vendor.
- Future review conditions: volume, transformation latency, or 3D formats require a specialized pipeline.

## Prompt 7 implementation note

Implemented private quarantine admission with server-generated paths, MIME/extension/size/image checks, checksum and dimensions. Hosted and external records remain distinct; provider parsing never fetches or downloads. Processing/variant tables are present but transformation and signed delivery are deferred. Publication is versioned and requires moderation plus effective hosting/embedding rights.
