export type PublicMetadata = {
    title: string;
    description: string;
    canonicalUrl: string | null;
    openGraphType: 'website';
    robots: string;
    themeColor: string;
};

export type StructuredData = Record<string, unknown>;

export type PublicSiteProps = {
    name: string;
    currentYear: number;
    registrationAvailable: boolean;
    repositoryUrl: string | null;
    metadata: PublicMetadata;
    structuredData: StructuredData[];
};

export type PublicPageProps = {
    publicSite: PublicSiteProps;
};

export type ExperienceMedia = {
    tmdb: {
        enabled: boolean;
        attribution: string | null;
        notice: string | null;
        images: Array<{
            key: string;
            alt: string;
            src: string;
            srcSet: string;
            width: number;
            height: number;
        }>;
    };
    transmission: {
        title: string;
        description: string | null;
        provider: 'youtube';
        embedUrl: string;
        canonicalUrl: string;
        attribution: string | null;
    } | null;
};
