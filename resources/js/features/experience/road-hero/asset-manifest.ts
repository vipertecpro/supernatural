export type RoadHeroAsset = {
    readonly id: string;
    readonly name: string;
    readonly kind: 'procedural' | 'font';
    readonly creator: string;
    readonly sourceUrl: string;
    readonly license: string;
    readonly commercialUsePermitted: boolean;
    readonly modificationPermitted: boolean;
    readonly attributionRequired: boolean;
    readonly repositoryRedistributionPermitted: boolean;
    readonly derivative: boolean;
    readonly format: string;
    readonly sizeBytes: number | null;
    readonly fallback: string;
};

const original = (
    id: string,
    name: string,
    format: string,
    fallback: string,
): RoadHeroAsset => ({
    id,
    name,
    kind: 'procedural',
    creator: 'Bankai / this repository',
    sourceUrl: 'local://resources/js/features/experience/road-hero',
    license: 'Original repository source',
    commercialUsePermitted: true,
    modificationPermitted: true,
    attributionRequired: false,
    repositoryRedistributionPermitted: true,
    derivative: false,
    format,
    sizeBytes: null,
    fallback,
});

export const roadHeroAssets = [
    original(
        'archive-roadster',
        'Archive Roadster',
        'Three.js geometry',
        'road-hero-fallback',
    ),
    original(
        'wet-forest-road',
        'Wet forest road',
        'Three.js geometry and materials',
        'road-hero-fallback',
    ),
    original(
        'forest-silhouettes',
        'Forest silhouettes',
        'Three.js instanced geometry',
        'road-hero-fallback',
    ),
    original(
        'road-weather',
        'Fog, rain, clouds, and sky',
        'Three.js particles and materials',
        'road-hero-fallback',
    ),
    original(
        'archive-entrance',
        'Archive entrance',
        'Three.js geometry and emissive materials',
        'road-hero-fallback',
    ),
    original(
        'procedural-soundtrack',
        'Procedural soundtrack',
        'Web Audio graph',
        'silent-mode',
    ),
    original(
        'road-hero-fallback',
        'Static fallback composition',
        'JSX and CSS',
        'semantic-copy',
    ),
    {
        id: 'cinzel-decorative',
        name: 'Cinzel Decorative 700',
        kind: 'font',
        creator: 'Natanael Gama via Fontsource',
        sourceUrl: 'https://fontsource.org/fonts/cinzel-decorative',
        license: 'OFL-1.1',
        commercialUsePermitted: true,
        modificationPermitted: true,
        attributionRequired: false,
        repositoryRedistributionPermitted: true,
        derivative: false,
        format: 'WOFF2 and WOFF',
        sizeBytes: null,
        fallback: 'serif',
    },
    {
        id: 'cormorant-garamond',
        name: 'Cormorant Garamond 500/600',
        kind: 'font',
        creator: 'Christian Thalmann via Fontsource',
        sourceUrl: 'https://fontsource.org/fonts/cormorant-garamond',
        license: 'OFL-1.1',
        commercialUsePermitted: true,
        modificationPermitted: true,
        attributionRequired: false,
        repositoryRedistributionPermitted: true,
        derivative: false,
        format: 'WOFF2 and WOFF',
        sizeBytes: null,
        fallback: 'serif',
    },
    {
        id: 'special-elite',
        name: 'Special Elite 400',
        kind: 'font',
        creator: 'Astigmatic via Fontsource',
        sourceUrl: 'https://fontsource.org/fonts/special-elite',
        license: 'Apache-2.0',
        commercialUsePermitted: true,
        modificationPermitted: true,
        attributionRequired: true,
        repositoryRedistributionPermitted: true,
        derivative: false,
        format: 'WOFF2 and WOFF',
        sizeBytes: null,
        fallback: 'monospace',
    },
    {
        id: 'instrument-sans',
        name: 'Instrument Sans 400/500/600',
        kind: 'font',
        creator: 'Rodrigo Fuenzalida and Jordan Egstad via Fontsource',
        sourceUrl: 'https://fontsource.org/fonts/instrument-sans',
        license: 'OFL-1.1',
        commercialUsePermitted: true,
        modificationPermitted: true,
        attributionRequired: false,
        repositoryRedistributionPermitted: true,
        derivative: false,
        format: 'WOFF2 and WOFF',
        sizeBytes: null,
        fallback: 'sans-serif',
    },
] as const satisfies readonly RoadHeroAsset[];

export const roadHeroAssetIds = new Set(roadHeroAssets.map(({ id }) => id));
