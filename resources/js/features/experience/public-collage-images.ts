import commonsCollageImageData from './commons-collage-images.json';
import type { PublicSceneVariant } from './public-scene-variants';

export type PublicCollageImage = {
    id: string;
    src: string;
    sourceUrl: string;
    alt?: string;
    width: number;
    height: number;
    focalPosition?: string;
    title?: string;
    artist?: string;
    license?: string;
    category?: string;
    rightsStatus: 'user-directed-reference' | 'verified-reusable';
};

export const publicCollageImages: readonly PublicCollageImage[] = [
    {
        id: 'brothers-impala-night',
        src: '/media/editorial/pinterest/brothers-impala-night.jpg',
        sourceUrl:
            'https://i.pinimg.com/736x/26/03/69/26036977b2ae532d53f54aed339b8e1b.jpg',
        alt: 'Illustrated hunters resting with a black classic car at night.',
        width: 720,
        height: 1280,
        focalPosition: 'center 58%',
        rightsStatus: 'user-directed-reference',
    },
    {
        id: 'hunters-stargazing-illustration',
        src: '/media/editorial/pinterest/hunters-stargazing-illustration.jpg',
        sourceUrl: 'https://in.pinterest.com/pin/1126744400511889297/',
        alt: 'Illustrated hunters sitting on a parked car beneath a star-filled sky.',
        width: 482,
        height: 750,
        focalPosition: 'center 64%',
        rightsStatus: 'user-directed-reference',
    },
    {
        id: 'falling-stars-hunter',
        src: '/media/editorial/pinterest/falling-stars-hunter.jpg',
        sourceUrl: 'https://in.pinterest.com/pin/54184001763149725/',
        alt: 'A lone hunter watching a dark sky filled with falling stars.',
        width: 736,
        height: 1308,
        focalPosition: 'center 55%',
        rightsStatus: 'user-directed-reference',
    },
    {
        id: 'young-hunters',
        src: '/media/editorial/pinterest/young-hunters.jpg',
        sourceUrl: 'https://in.pinterest.com/pin/783767141418233561/',
        alt: 'Two young hunters standing together beside a car at night.',
        width: 564,
        height: 1002,
        focalPosition: 'center 55%',
        rightsStatus: 'user-directed-reference',
    },
    {
        id: 'celestial-hunter-art',
        src: '/media/editorial/pinterest/celestial-hunter-art.jpg',
        sourceUrl: 'https://in.pinterest.com/pin/1024287508994665421/',
        alt: 'Celestial fan artwork framing a hunter with stars, moons, and watchful eyes.',
        width: 736,
        height: 1308,
        focalPosition: 'center 52%',
        rightsStatus: 'user-directed-reference',
    },
    {
        id: 'crowned-hunter-art',
        src: '/media/editorial/pinterest/crowned-hunter-art.jpg',
        sourceUrl: 'https://in.pinterest.com/pin/646688827767151779/',
        alt: 'Dark illustrated hunter standing before an ornate crowned floral circle.',
        width: 640,
        height: 905,
        focalPosition: 'center 45%',
        rightsStatus: 'user-directed-reference',
    },
];

export const commonsCollageImages =
    commonsCollageImageData as unknown as readonly PublicCollageImage[];

export const supernaturalMinimalImages: readonly PublicCollageImage[] = [
    ...publicCollageImages,
    ...commonsCollageImages,
];

const collageVariantOrder: readonly PublicSceneVariant[] = [
    'archive',
    'knowledge',
    'system',
    'signal',
    'boundary',
    'rights',
];

/** Returns the complete supernatural image library, rotated for each route family. */
export function resolvePublicCollageImages(
    variant: PublicSceneVariant,
): readonly PublicCollageImage[] {
    const groupIndex = Math.max(0, collageVariantOrder.indexOf(variant));
    const routeOffset = groupIndex * 11;

    return [
        ...supernaturalMinimalImages.slice(routeOffset),
        ...supernaturalMinimalImages.slice(0, routeOffset),
    ];
}
