export type RightsDecision = 'allowed' | 'prohibited' | 'unknown';

export type ExperienceAsset = {
    key: string;
    type: 'procedural-visual' | 'font' | 'audio' | 'image' | 'video-embed';
    source: string;
    sourceUrl: string | null;
    creatorOrProvider: string;
    usageBasis: string;
    attributionText: string | null;
    hostingPermission: RightsDecision;
    embeddingPermission: RightsDecision;
    derivativePermission: RightsDecision;
    commercialUse: RightsDecision;
    expiresAt: string | null;
    delivery: 'local' | 'remote' | 'procedural';
    fallbackAsset: string | null;
    public: boolean;
    reviewStatus: 'approved' | 'pending' | 'rejected';
};

export const experienceAssets: readonly ExperienceAsset[] = [
    {
        key: 'procedural-archive-environment',
        type: 'procedural-visual',
        source: 'Created in this repository',
        sourceUrl: null,
        creatorOrProvider: 'The Archive project',
        usageBasis: 'Original procedural geometry, shaders, CSS, and SVG',
        attributionText: null,
        hostingPermission: 'allowed',
        embeddingPermission: 'prohibited',
        derivativePermission: 'allowed',
        commercialUse: 'allowed',
        expiresAt: null,
        delivery: 'procedural',
        fallbackAsset: 'css-archive-environment',
        public: true,
        reviewStatus: 'approved',
    },
    ...(
        [
            ['font-instrument-sans', 'Instrument Sans', 'OFL-1.1'],
            ['font-cinzel-decorative', 'Cinzel Decorative', 'OFL-1.1'],
            ['font-cormorant-garamond', 'Cormorant Garamond', 'OFL-1.1'],
            ['font-special-elite', 'Special Elite', 'Apache-2.0'],
        ] as const
    ).map(([key, name, licence]) => ({
        key,
        type: 'font' as const,
        source: '@fontsource npm package',
        sourceUrl: `https://fontsource.org/fonts/${name.toLowerCase().replaceAll(' ', '-')}`,
        creatorOrProvider: 'Fontsource and original typeface authors',
        usageBasis: licence,
        attributionText: `${name} — ${licence}`,
        hostingPermission: 'allowed' as const,
        embeddingPermission: 'prohibited' as const,
        derivativePermission: 'allowed' as const,
        commercialUse: 'allowed' as const,
        expiresAt: null,
        delivery: 'local' as const,
        fallbackAsset: 'system-font-stack',
        public: true,
        reviewStatus: 'approved' as const,
    })),
];

export const validateExperienceAssets = (
    assets: readonly ExperienceAsset[],
): void => {
    const keys = new Set<string>();

    assets.forEach((asset) => {
        if (keys.has(asset.key)) {
            throw new Error(`Duplicate experience asset key: ${asset.key}`);
        }

        keys.add(asset.key);

        if (
            asset.reviewStatus !== 'approved' ||
            asset.hostingPermission === 'unknown' ||
            asset.commercialUse === 'unknown' ||
            !asset.source ||
            !asset.creatorOrProvider ||
            !asset.usageBasis ||
            (asset.delivery === 'remote' && !asset.sourceUrl)
        ) {
            throw new Error(
                `Experience asset lacks approved rights metadata: ${asset.key}`,
            );
        }
    });
};

validateExperienceAssets(experienceAssets);
