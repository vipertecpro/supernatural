import type { ReactNode } from 'react';

export function PublicPageIntro({
    eyebrow,
    title,
    description,
    children,
}: {
    eyebrow: string;
    title: string;
    description: string;
    children?: ReactNode;
}) {
    return (
        <header className="border-b border-border-subtle pb-10">
            <p className="text-metadata text-foreground-evidence">{eyebrow}</p>
            <h1 className="text-display-md mt-5 text-balance">{title}</h1>
            <p className="text-body-lg mt-6 max-w-2xl text-pretty text-foreground-secondary">
                {description}
            </p>
            {children}
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
        <section className="public-article-section">
            <h2>{title}</h2>
            {children}
        </section>
    );
}
