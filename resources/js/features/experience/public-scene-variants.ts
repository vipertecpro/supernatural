export type PublicSceneVariant =
    'archive' | 'knowledge' | 'system' | 'signal' | 'boundary' | 'rights';

export function resolvePublicSceneVariant(url: string): PublicSceneVariant {
    if (url.startsWith('/about')) {
        return 'knowledge';
    }

    if (url.startsWith('/open-source')) {
        return 'system';
    }

    if (url.startsWith('/accessibility')) {
        return 'signal';
    }

    if (url.startsWith('/content-policy')) {
        return 'boundary';
    }

    if (url.startsWith('/copyright-and-takedown')) {
        return 'rights';
    }

    return 'archive';
}
