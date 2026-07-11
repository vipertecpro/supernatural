# Prompt 3 Architecture Quality Review

Date: 2026-07-12

## Review results

- Canonical names: reconciled against `03-database-schema.md`; specialized documents use the same roots.
- Dependencies: 17-module dependency shape has no required synchronous cycle. Search/Notifications are downstream projections; moderation effects return through owned restriction interfaces/events.
- Integrity: durable catalog/lore endpoints use explicit FKs. Morphs are limited to genuinely cross-cutting subjects and require a stable allowlisted morph map.
- Ownership: every table group has one owner; Bunker roles are not platform roles; Web/API/mobile call the same actions/policies.
- Sources/rights: citations cover records, fields, revisions, claims and graph assertions; unknown rights deny hosting/derivatives.
- Spoilers: normalized boundaries and backend serialization/query filtering cover public, community, real-time, search, notification, quiz, board, AI and mobile contexts.
- Moderation/audit: UGC phases follow minimum moderation; important security/editorial/rights/moderation actions are attributable and privacy minimized.
- Scale: feed/message/graph/search queue indexes and cursor keys are specified; high-volume ephemeral events are not persisted indefinitely.
- Deletion: unsafe broad cascades are rejected; durable evidence/revisions/audit restrict deletion, user-facing content soft deletes, catalog archives.
- Publication: separate editorial, UGC, media, source and relationship lifecycles are explicit.
- Neutrality: no schema, enum, seed requirement, fixture, or API discriminator is Supernatural-specific.
- Sequence: catalog/editorial/spoiler/moderation prerequisites precede community/chat/watch rooms.

## Deliberate constraints / follow-up risks

The 179-table full vision is a roadmap, not a mandate to create all tables now. Each phase must revalidate real product requirements and may simplify unused future tables through a new ADR. Native MySQL enums inherited from Prompt 2 are acceptable but evolving workflow enums need careful migrations. Prompt 2's JSON spoiler boundary must be normalized only after Catalog IDs exist. The root license, retention/legal hold, providers, and production operations remain owner decisions.

## Conclusion

The blueprint is internally coherent and implementation-ready for the bounded Catalog Core. Repository readiness is conditional: Prompt 2 is uncommitted and its three migrations are pending locally, so preservation/review and intentional migration application are gates before Prompt 4 development.
