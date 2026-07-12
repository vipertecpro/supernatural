export type HomepageChapter = {
    id: string;
    eyebrow: string;
    title: string;
    description: string;
    points: string[];
    availability: 'Foundation implemented' | 'Public interface planned';
};

export const homepageChapters: HomepageChapter[] = [
    {
        id: 'archive-opens',
        eyebrow: '01 / The archive opens',
        title: 'A record is more useful when its connections survive.',
        description:
            'The platform models universes, works, seasons, episodes, lore, timelines, and sources as related records—not loose pages competing for context.',
        points: [
            'Structured catalog',
            'Localized records',
            'Timelines',
            'Sources and citations',
        ],
        availability: 'Public interface planned',
    },
    {
        id: 'journey',
        eyebrow: '02 / Follow your journey',
        title: 'Remember where you were, without making it public.',
        description:
            'Progress, viewing orders, rewatches, watchlists, favourites, ratings, and private notes form a personal path through connected stories. Journey data is private by default.',
        points: [
            'Continue watching',
            'Rewatch history',
            'Private notes',
            'Spoiler-aware visibility',
        ],
        availability: 'Foundation implemented',
    },
    {
        id: 'connections',
        eyebrow: '03 / Knowledge has connections',
        title: 'People, places, objects, and events belong in a living graph.',
        description:
            'Typed relationships connect characters, places, artifacts, organizations, appearances, and timelines back to reviewable evidence.',
        points: [
            'Bounded relationships',
            'Appearance records',
            'Evidence links',
            'Structured alternatives',
        ],
        availability: 'Public interface planned',
    },
    {
        id: 'spoilers',
        eyebrow: '04 / Spoilers respect your progress',
        title: 'The archive should never know more than you asked to see.',
        description:
            'Viewing boundaries and tolerance preferences produce visible, warning, redacted, or hidden states. Conservative fallbacks withhold protected details rather than merely blurring them.',
        points: [
            'Progress boundaries',
            'Explicit warnings',
            'True redaction',
            'Conservative fallback',
        ],
        availability: 'Foundation implemented',
    },
    {
        id: 'bunkers',
        eyebrow: '05 / Find your bunker',
        title: 'Gather around a shared world, with local rules and clear boundaries.',
        description:
            'Public, private, and invite-only groups support posts, comments, polls, mentions, local roles, blocking, muting, reporting, and moderation.',
        points: [
            'Membership privacy',
            'Local roles',
            'Interaction safety',
            'Moderation paths',
        ],
        availability: 'Public interface planned',
    },
    {
        id: 'evidence',
        eyebrow: '06 / Knowledge with evidence',
        title: 'A claim becomes trustworthy when its trail remains visible.',
        description:
            'Contributors propose attributable revisions with sources, citations, rights assessments, spoiler classifications, review decisions, and explicit publication control.',
        points: [
            'Attributable revisions',
            'Rights review',
            'Editorial decisions',
            'Publication gates',
        ],
        availability: 'Foundation implemented',
    },
];

export const plannedCapabilities = [
    'Persistent messaging and chat',
    'Presence and typing indicators',
    'Watch Rooms and Case Boards',
    'Gamification and events',
    'NativePHP mobile application',
    'Push notifications',
    'Full operational workspaces',
] as const;
