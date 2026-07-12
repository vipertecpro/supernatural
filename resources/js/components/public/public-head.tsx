import { Head } from '@inertiajs/react';
import type { PublicSiteProps } from '@/types';

export function PublicHead({ publicSite }: { publicSite: PublicSiteProps }) {
    const { metadata, name, structuredData } = publicSite;

    return (
        <Head title={metadata.title}>
            <meta
                head-key="description"
                name="description"
                content={metadata.description}
            />
            <meta head-key="robots" name="robots" content={metadata.robots} />
            <meta
                head-key="application-name"
                name="application-name"
                content={name}
            />
            <meta
                head-key="theme-color"
                name="theme-color"
                content={metadata.themeColor}
            />
            <meta
                head-key="og:title"
                property="og:title"
                content={metadata.title}
            />
            <meta
                head-key="og:description"
                property="og:description"
                content={metadata.description}
            />
            <meta
                head-key="og:type"
                property="og:type"
                content={metadata.openGraphType}
            />
            <meta
                head-key="twitter:card"
                name="twitter:card"
                content="summary"
            />
            <meta
                head-key="twitter:title"
                name="twitter:title"
                content={metadata.title}
            />
            <meta
                head-key="twitter:description"
                name="twitter:description"
                content={metadata.description}
            />
            {metadata.canonicalUrl && (
                <>
                    <link
                        head-key="canonical"
                        rel="canonical"
                        href={metadata.canonicalUrl}
                    />
                    <meta
                        head-key="og:url"
                        property="og:url"
                        content={metadata.canonicalUrl}
                    />
                </>
            )}
            {structuredData.map((entry, index) => (
                <script
                    head-key={`structured-data-${index}`}
                    key={`${String(entry['@type'])}-${index}`}
                    type="application/ld+json"
                >
                    {JSON.stringify(entry).replaceAll('<', '\\u003c')}
                </script>
            ))}
        </Head>
    );
}
