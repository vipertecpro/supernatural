import type { ReactNode } from 'react';
import { resolvePublicCollageImages } from '@/features/experience/public-collage-images';
import type { PublicSceneVariant } from '@/features/experience/public-scene-variants';

const variantLabels: Record<PublicSceneVariant, string> = {
    archive: 'AR',
    knowledge: 'KN',
    system: 'SY',
    signal: 'SG',
    boundary: 'BD',
    rights: 'RT',
};

export function PublicPageIntro({
    eyebrow,
    title,
    description,
    children,
    variant = 'archive',
}: {
    eyebrow: string;
    title: string;
    description: string;
    children?: ReactNode;
    variant?: PublicSceneVariant;
}) {
    const titleWords = title.split(' ');
    const breakAt = Math.ceil(titleWords.length / 2);
    const titleLines = [
        titleWords.slice(0, breakAt).join(' '),
        titleWords.slice(breakAt).join(' '),
    ].filter(Boolean);
    const heroImage = resolvePublicCollageImages(variant)[0];

    return (
        <header className="immersive-page-intro" data-page-variant={variant}>
            <div className="immersive-page-intro-copy">
                <div className="immersive-page-kicker" data-page-kicker>
                    <span aria-hidden="true" />
                    <p className="text-metadata text-foreground-evidence">
                        {eyebrow}
                    </p>
                </div>
                <h1 className="immersive-page-title text-display-md mt-5 text-balance">
                    {titleLines.map((line, index) => (
                        <span key={line}>
                            {line}
                            {index < titleLines.length - 1 ? ' ' : ''}
                        </span>
                    ))}
                </h1>
                <p className="immersive-page-description text-body-lg mt-6 max-w-2xl text-pretty text-foreground-secondary">
                    {description}
                </p>
                {children}
            </div>
            <figure className="immersive-page-hero-media">
                <img
                    src={heroImage.src}
                    alt={heroImage.alt ?? ''}
                    width={heroImage.width}
                    height={heroImage.height}
                    decoding="async"
                    fetchPriority="high"
                    style={{ objectPosition: heroImage.focalPosition }}
                />
                <figcaption>
                    <span>({variantLabels[variant]})</span>
                    <span>ARCHIVE / VISUAL RECORD</span>
                </figcaption>
            </figure>
            <div className="immersive-page-coordinate" aria-hidden="true">
                <span>LAT 00.000</span>
                <span>LON 00.000</span>
                <span>REC / ACTIVE</span>
            </div>
        </header>
    );
}

export function PublicArticleSection({
    title,
    children,
    image,
}: {
    title: string;
    children: ReactNode;
    image?: {
        src: string;
        alt?: string;
        focalPosition?: string;
    };
}) {
    return (
        <section
            className="public-article-section"
            data-immersive-section
            data-has-media={Boolean(image)}
        >
            <div className="public-article-content">
                <div className="public-article-metadata" aria-hidden="true">
                    <span>ARCHIVE RECORD</span>
                    <span>FIELD NOTE / ACTIVE</span>
                </div>
                <h2>{title}</h2>
                {children}
            </div>
            {image ? (
                <figure className="public-article-media">
                    <img
                        src={image.src}
                        alt={image.alt ?? ''}
                        loading="lazy"
                        decoding="async"
                        style={{ objectPosition: image.focalPosition }}
                    />
                </figure>
            ) : null}
        </section>
    );
}
