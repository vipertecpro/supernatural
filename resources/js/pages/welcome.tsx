import { Link, usePage } from '@inertiajs/react';
import {
    ArrowDown,
    ArrowRight,
    BadgeCheck,
    Blocks,
    Code2,
    Eye,
    GitBranch,
    LockKeyhole,
    Scale,
    ShieldCheck,
} from 'lucide-react';
import { ArchiveHeroScene } from '@/components/public/archive-hero-scene';
import {
    ArchiveRecordStack,
    BunkerNetworkPreview,
    EvidenceGraphPreview,
    JourneyPathPreview,
    SourceLedgerPreview,
    SpoilerStatePreview,
} from '@/components/public/feature-previews';
import { MediaInterlude } from '@/components/public/media-interlude';
import { PublicHead } from '@/components/public/public-head';
import { ScrollChapter } from '@/components/public/scroll-chapter';
import { Button } from '@/components/ui/button';
import { homepageChapters, plannedCapabilities } from '@/content/public-site';
import { CinematicPreloader } from '@/features/experience/cinematic-preloader';
import { useExperience } from '@/features/experience/experience-context';
import { HeroSoundControl } from '@/features/experience/hero-sound-control';
import { HomepageChoreography } from '@/features/experience/homepage-choreography';
import { about, dashboard, openSource, register } from '@/routes';
import type { ExperienceMedia, PublicPageProps } from '@/types';

const previews = [
    ArchiveRecordStack,
    JourneyPathPreview,
    EvidenceGraphPreview,
    SpoilerStatePreview,
    BunkerNetworkPreview,
    SourceLedgerPreview,
] as const;

export default function Welcome({
    publicSite,
    experienceMedia,
}: PublicPageProps & { experienceMedia: ExperienceMedia }) {
    const { auth } = usePage().props;
    const { mode, quality } = useExperience();

    return (
        <>
            <CinematicPreloader />
            <HomepageChoreography />
            <PublicHead publicSite={publicSite} />
            <section className="archive-hero relative isolate min-h-[calc(100svh-var(--shell-header-height))] overflow-hidden border-b border-border-subtle">
                <ArchiveHeroScene />
                <div className="relative z-10 mx-auto grid min-h-[calc(100svh-var(--shell-header-height))] max-w-(--content-wide) content-end gap-10 px-4 py-12 sm:px-6 md:py-16 lg:grid-cols-[minmax(0,1fr)_minmax(22rem,0.7fr)] lg:items-end lg:gap-16 lg:py-20">
                    <div className="max-w-4xl" data-hero-reveal>
                        <a
                            href="#archive-opens"
                            className="sr-only focus:not-sr-only"
                        >
                            Skip the introduction
                        </a>
                        <div className="archive-hero-status-row">
                            <div className="archive-hero-status">
                                <span aria-hidden="true" />
                                {mode === 'reduced'
                                    ? 'Reduced motion active'
                                    : 'Cinematic systems online'}
                            </div>
                            <div className="archive-hero-tier">
                                {mode} / {quality}
                            </div>
                        </div>
                        <p className="text-label mt-8 tracking-[0.2em] text-foreground-evidence uppercase">
                            A living digital archive
                        </p>
                        <h1 className="archive-hero-title text-display-lg mt-4 max-w-5xl text-balance">
                            <span>Every story</span>
                            <span>leaves a trail.</span>
                        </h1>
                        <p className="text-body-lg mt-6 max-w-2xl text-pretty text-foreground-secondary">
                            The Archive connects fictional worlds, lore, private
                            progress, evidence, and safer communities into one
                            evolving platform.
                        </p>
                        <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                            <Button size="lg" asChild>
                                <Link
                                    href={auth.user ? dashboard() : register()}
                                >
                                    {auth.user
                                        ? 'Continue to dashboard'
                                        : 'Create your archive'}
                                    <ArrowRight data-icon="inline-end" />
                                </Link>
                            </Button>
                            <Button size="lg" variant="outline" asChild>
                                <a href="#archive-opens">
                                    See how it works{' '}
                                    <ArrowDown data-icon="inline-end" />
                                </a>
                            </Button>
                            <HeroSoundControl />
                        </div>
                    </div>
                    <div
                        className="hidden justify-self-end lg:block"
                        data-hero-reveal
                    >
                        <p className="text-metadata max-w-xs border-l border-border-strong pl-5 text-foreground-muted">
                            ORIGINAL SYSTEM / 001
                            <br />
                            CSS, SVG, and semantic HTML. No remote media,
                            soundtrack, or franchise artwork.
                        </p>
                    </div>
                </div>
            </section>

            <div className="public-narrative">
                {homepageChapters.map((chapter, index) => {
                    const Preview = previews[index];

                    return (
                        <ScrollChapter
                            key={chapter.id}
                            id={chapter.id}
                            data-scene-index={index + 1}
                            aria-labelledby={`${chapter.id}-title`}
                        >
                            <span
                                className="public-chapter-index"
                                aria-hidden="true"
                            >
                                {String(index + 1).padStart(2, '0')}
                            </span>
                            <div className="public-chapter-inner mx-auto grid max-w-(--content-wide) gap-10 px-4 py-20 sm:px-6 md:py-28 lg:grid-cols-12 lg:items-center lg:gap-12">
                                <div
                                    className={`public-chapter-copy lg:col-span-5 ${index % 2 ? 'lg:order-2 lg:col-start-8' : ''}`}
                                >
                                    <p className="text-metadata text-foreground-evidence">
                                        {chapter.eyebrow}
                                    </p>
                                    <h2
                                        id={`${chapter.id}-title`}
                                        className="text-display-md mt-5 text-balance"
                                    >
                                        {chapter.title}
                                    </h2>
                                    <p className="text-body-lg mt-6 text-pretty text-foreground-secondary">
                                        {chapter.description}
                                    </p>
                                    <ul className="mt-7 grid gap-3 sm:grid-cols-2">
                                        {chapter.points.map((point) => (
                                            <li
                                                key={point}
                                                className="flex items-center gap-2 text-sm text-foreground-secondary"
                                            >
                                                <span
                                                    className="size-1.5 rounded-full bg-foreground-evidence"
                                                    aria-hidden="true"
                                                />
                                                {point}
                                            </li>
                                        ))}
                                    </ul>
                                    <p className="text-metadata mt-8 inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-foreground-muted">
                                        {chapter.availability ===
                                        'Foundation implemented' ? (
                                            <BadgeCheck className="size-3.5 text-success" />
                                        ) : (
                                            <Blocks className="size-3.5" />
                                        )}
                                        {chapter.availability}
                                    </p>
                                </div>
                                <div
                                    className={`public-chapter-visual lg:col-span-6 ${index % 2 ? 'lg:order-1' : 'lg:col-start-7'}`}
                                >
                                    <Preview />
                                </div>
                            </div>
                        </ScrollChapter>
                    );
                })}

                <MediaInterlude media={experienceMedia} />

                <ScrollChapter
                    id="safety"
                    aria-labelledby="safety-title"
                    className="border-y border-border-subtle bg-background-inset/55"
                >
                    <div className="mx-auto max-w-(--content-wide) px-4 py-20 sm:px-6 md:py-28">
                        <p className="text-metadata text-foreground-evidence">
                            07 / Built for safety
                        </p>
                        <div className="mt-5 grid gap-10 lg:grid-cols-[1fr_1.2fr] lg:items-end">
                            <div>
                                <h2
                                    id="safety-title"
                                    className="text-display-md text-balance"
                                >
                                    Safety is infrastructure, not a footer
                                    promise.
                                </h2>
                                <p className="text-body-lg mt-6 text-foreground-secondary">
                                    Reports, restrictions, appeals, private
                                    block and mute lists, rights-aware media,
                                    spoiler-safe notifications, and conservative
                                    privacy defaults are modeled as product
                                    systems.
                                </p>
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                {[
                                    [
                                        ShieldCheck,
                                        'Moderation',
                                        'Attributable cases, actions, restrictions, and proportionate appeals.',
                                    ],
                                    [
                                        LockKeyhole,
                                        'Privacy',
                                        'Personal activity, reports, blocks, and mutes remain scoped and private.',
                                    ],
                                    [
                                        Scale,
                                        'Rights',
                                        'Hosting, embedding, attribution, and takedown remain separate decisions.',
                                    ],
                                    [
                                        Eye,
                                        'Spoilers',
                                        'The same visibility boundary follows search, notifications, and community content.',
                                    ],
                                ].map(([Icon, title, copy]) => (
                                    <div
                                        key={String(title)}
                                        className="rounded-xl border bg-surface-primary p-5 shadow-surface"
                                    >
                                        <Icon
                                            className="size-5 text-foreground-evidence"
                                            aria-hidden="true"
                                        />
                                        <h3 className="mt-5 font-semibold">
                                            {String(title)}
                                        </h3>
                                        <p className="mt-2 text-sm leading-6 text-foreground-secondary">
                                            {String(copy)}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </ScrollChapter>

                <ScrollChapter
                    id="open-source"
                    aria-labelledby="open-source-title"
                >
                    <div className="mx-auto grid max-w-(--content-wide) gap-12 px-4 py-20 sm:px-6 md:py-28 lg:grid-cols-12">
                        <div className="lg:col-span-6">
                            <p className="text-metadata text-foreground-evidence">
                                08 / Open by design
                            </p>
                            <h2
                                id="open-source-title"
                                className="text-display-md mt-5 text-balance"
                            >
                                Built in public, with the hard boundaries
                                visible.
                            </h2>
                            <p className="text-body-lg mt-6 text-foreground-secondary">
                                Laravel, React, Inertia, TypeScript, modular
                                architecture, automated tests, static analysis,
                                accessibility targets, and security-first domain
                                boundaries form the current foundation.
                                NativePHP is a future client—not a shipped
                                feature.
                            </p>
                            <Button className="mt-8" variant="outline" asChild>
                                <Link href={openSource()}>
                                    Explore the architecture{' '}
                                    <ArrowRight data-icon="inline-end" />
                                </Link>
                            </Button>
                        </div>
                        <div className="lg:col-span-5 lg:col-start-8">
                            <div className="rounded-xl border bg-surface-primary p-6 shadow-surface">
                                <div className="flex items-center gap-3 border-b pb-5">
                                    <Code2 className="size-5" />
                                    <span className="text-metadata">
                                        PLATFORM / STATUS
                                    </span>
                                </div>
                                <ul className="mt-5 grid gap-3 text-sm text-foreground-secondary">
                                    {[
                                        'Laravel 13 modular monolith',
                                        'Inertia 3 + React 19',
                                        'Typed route contracts',
                                        'Pest 4 regression suite',
                                        'WCAG 2.2 AA implementation target',
                                        'No approved software licence yet',
                                    ].map((item) => (
                                        <li key={item} className="flex gap-3">
                                            <GitBranch className="mt-0.5 size-4 shrink-0 text-foreground-evidence" />
                                            {item}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </div>
                </ScrollChapter>

                <ScrollChapter
                    id="roadmap"
                    aria-labelledby="roadmap-title"
                    className="border-t border-border-subtle"
                >
                    <div className="mx-auto max-w-(--content-wide) px-4 py-16 sm:px-6 md:py-20">
                        <div className="grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
                            <div>
                                <p className="text-metadata text-foreground-evidence">
                                    TRANSPARENT ROADMAP
                                </p>
                                <h2
                                    id="roadmap-title"
                                    className="text-section-title mt-4"
                                >
                                    Planned, not presented as live
                                </h2>
                            </div>
                            <ul className="grid gap-x-8 gap-y-3 sm:grid-cols-2">
                                {plannedCapabilities.map((item) => (
                                    <li
                                        key={item}
                                        className="flex items-center gap-2 text-sm text-foreground-muted"
                                    >
                                        <span aria-hidden="true">○</span>
                                        {item}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </ScrollChapter>

                <section
                    className="archive-final-cta border-t border-border-subtle"
                    aria-labelledby="final-cta-title"
                >
                    <div className="mx-auto flex max-w-(--content-wide) flex-col gap-8 px-4 py-20 sm:px-6 md:py-28 lg:flex-row lg:items-end lg:justify-between">
                        <div className="max-w-3xl">
                            <p className="text-metadata text-foreground-evidence">
                                THE NEXT RECORD IS YOURS
                            </p>
                            <h2
                                id="final-cta-title"
                                className="text-display-md mt-5 text-balance"
                            >
                                Enter the archive before every door is open.
                            </h2>
                            <p className="text-body-lg mt-5 text-foreground-secondary">
                                Create an account for the implemented
                                authentication and onboarding experience, or
                                read the thinking behind the platform.
                            </p>
                        </div>
                        <div className="flex flex-col gap-3 sm:flex-row">
                            <Button size="lg" asChild>
                                <Link
                                    href={auth.user ? dashboard() : register()}
                                >
                                    {auth.user
                                        ? 'Open dashboard'
                                        : 'Create account'}
                                    <ArrowRight />
                                </Link>
                            </Button>
                            <Button size="lg" variant="outline" asChild>
                                <Link href={about()}>About the project</Link>
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}
