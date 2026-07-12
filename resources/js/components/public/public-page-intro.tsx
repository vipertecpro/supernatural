import type { ReactNode } from 'react';
import type { PublicSceneVariant } from '@/features/experience/public-scene-variants';

function PageGlyph({ variant }: { variant: PublicSceneVariant }) {
    return (
        <div
            className="immersive-page-glyph"
            data-glyph={variant}
            aria-hidden="true"
        >
            <span className="immersive-glyph-ring immersive-glyph-ring-one" />
            <span className="immersive-glyph-ring immersive-glyph-ring-two" />
            <span className="immersive-glyph-ring immersive-glyph-ring-three" />
            <span className="immersive-glyph-axis immersive-glyph-axis-x" />
            <span className="immersive-glyph-axis immersive-glyph-axis-y" />
            <span className="immersive-glyph-core" />
            <span className="immersive-glyph-code">{variant.slice(0, 3)}</span>
        </div>
    );
}

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
            <PageGlyph variant={variant} />
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
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <section className="public-article-section" data-immersive-section>
            <h2>{title}</h2>
            {children}
        </section>
    );
}
