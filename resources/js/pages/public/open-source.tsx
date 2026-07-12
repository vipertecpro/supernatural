import { ExternalLink } from 'lucide-react';
import { PublicHead } from '@/components/public/public-head';
import {
    PublicArticleSection,
    PublicPageIntro,
} from '@/components/public/public-page-intro';
import { Button } from '@/components/ui/button';
import type { PublicPageProps } from '@/types';

const stack = [
    [
        'Laravel 13',
        'Domain ownership, APIs, authorization, jobs, events, and a modular-monolith boundary.',
    ],
    [
        'React 19 + Inertia 3',
        'Server-driven page contracts with a focused client experience and no duplicated SPA API layer.',
    ],
    [
        'TypeScript + Wayfinder',
        'Typed page data and generated route actions instead of hand-written application URLs.',
    ],
    [
        'Pest + Larastan',
        'Behavioral regression coverage and static analysis as part of the implementation gate.',
    ],
] as const;

export default function OpenSource({ publicSite }: PublicPageProps) {
    return (
        <>
            <PublicHead publicSite={publicSite} />
            <PublicPageIntro
                eyebrow="OPEN SOURCE / ENGINEERING"
                title="The architecture is part of the story."
                description="The Archive is being developed in public as a reusable, API-first fandom platform with explicit security, accessibility, evidence, and rights boundaries."
                variant="system"
            >
                {publicSite.repositoryUrl && (
                    <Button className="mt-8" asChild>
                        <a
                            href={publicSite.repositoryUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            View configured repository <ExternalLink />
                        </a>
                    </Button>
                )}
            </PublicPageIntro>
            <PublicArticleSection title="A modular monolith first">
                <p>
                    Catalog, Editorial, Spoilers, Lore, Media, Search, Journey,
                    Moderation, Notifications, Community, and Identity Safety
                    have explicit ownership inside one deployable Laravel
                    application. This keeps transactions and policy boundaries
                    legible before distributed infrastructure is justified.
                </p>
            </PublicArticleSection>
            <section
                className="my-12 grid gap-4 sm:grid-cols-2"
                data-immersive-section
                aria-labelledby="stack-title"
            >
                <h2 id="stack-title" className="sr-only">
                    Technology stack
                </h2>
                {stack.map(([title, copy]) => (
                    <div
                        key={title}
                        className="rounded-xl border bg-surface-primary p-5 shadow-surface"
                    >
                        <h3 className="font-semibold">{title}</h3>
                        <p className="mt-2 text-sm leading-6 text-foreground-secondary">
                            {copy}
                        </p>
                    </div>
                ))}
            </section>
            <PublicArticleSection title="API-first contracts and a future client">
                <p>
                    The versioned API is the stable boundary for public, owner,
                    community, editorial, and moderation resources. NativePHP
                    Mobile 4 is planned as a future client after the web product
                    and operational contracts mature; it is not currently
                    implemented.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="Quality, accessibility, and security">
                <p>
                    Every implemented phase includes focused and full tests,
                    static analysis, formatting, frontend type checks,
                    production builds, dependency audits, cache validation,
                    responsive review, and a threat boundary. The accessibility
                    target is WCAG 2.2 AA, not a certification claim.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="Contributing responsibly">
                <p>
                    Contributions must preserve fandom neutrality, authorization
                    and privacy boundaries, source and rights governance,
                    spoiler filtering, accessible alternatives, and the
                    published implementation sequence. Copyrighted franchise
                    assets, copied transcripts, scraped websites, secrets,
                    unsafe embeds, and unreviewed external dependencies do not
                    belong in the repository.
                </p>
            </PublicArticleSection>
            <aside
                className="public-note"
                data-immersive-section
                aria-label="Software licence status"
            >
                <h2>Source available does not mean licensed for reuse</h2>
                <p>
                    No standalone project-wide software licence has been
                    approved. A legacy package-manifest value is not an owner
                    licensing decision. Until a licence is selected, source
                    availability alone does not grant permission to copy,
                    modify, or redistribute this software. Third-party content
                    rights remain separate under any future software licence.
                </p>
            </aside>
        </>
    );
}
