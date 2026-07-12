import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { PublicHead } from '@/components/public/public-head';
import {
    PublicArticleSection,
    PublicPageIntro,
} from '@/components/public/public-page-intro';
import { Button } from '@/components/ui/button';
import { openSource, register } from '@/routes';
import type { PublicPageProps } from '@/types';

export default function About({ publicSite }: PublicPageProps) {
    return (
        <>
            <PublicHead publicSite={publicSite} />
            <PublicPageIntro
                eyebrow="ABOUT / THE ARCHIVE"
                title="A living record, not another pile of pages."
                description="The Archive is a fandom-neutral platform foundation for understanding connected fictional worlds while respecting progress, evidence, privacy, and community safety."
            />
            <PublicArticleSection title="Why a static wiki is not enough">
                <p>
                    A conventional page can explain one subject well, but
                    stories rarely live alone. Works belong to universes, people
                    appear across events, objects change hands, timelines
                    disagree, and every claim carries a source and confidence
                    boundary. The Archive models those relationships directly so
                    context can survive every edit.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="Knowledge with structure">
                <p>
                    The implemented backend foundation connects catalog records,
                    typed lore entities, relationships, appearances, timelines,
                    sources, citations, rights decisions, revisions, and
                    publication controls. The cinematic public Catalog and Lore
                    interfaces remain the next frontend phase.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="A private journey through public knowledge">
                <p>
                    Progress, viewing orders, rewatches, favourites, ratings,
                    watchlists, and notes are designed as personal context—not
                    public decoration. Journey information is private by
                    default, and spoiler visibility follows the viewer’s actual
                    boundary.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="Community without blurred authority">
                <p>
                    Bunkers give groups local identity, membership, posts,
                    comments, polls, and roles. Platform moderation, contributor
                    review, and group authority remain separate. Blocking,
                    muting, reports, restrictions, and appeals are built into
                    the domain foundation rather than deferred to social
                    convention.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="Evidence before certainty">
                <p>
                    Contributors propose attributable changes. Sources,
                    citations, rights assessments, spoiler classifications,
                    reviews, and publication decisions stay visible as distinct
                    states. Fan interpretation must never masquerade as reviewed
                    fact.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="Open engineering, honest status">
                <p>
                    The platform is built as a Laravel modular monolith with
                    React, Inertia, TypeScript, and a versioned API contract.
                    Authentication, onboarding, and substantial backend domains
                    exist today. Public knowledge screens, operational
                    workspaces, messaging, real-time rooms, and the future
                    mobile client are not presented as shipped.
                </p>
                <div className="mt-8 flex flex-wrap gap-3">
                    <Button asChild>
                        <Link href={register()}>
                            Create an account <ArrowRight />
                        </Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href={openSource()}>
                            Open-source architecture
                        </Link>
                    </Button>
                </div>
            </PublicArticleSection>
            <aside
                className="public-note"
                aria-label="Unofficial project disclaimer"
            >
                <h2>Unofficial by design</h2>
                <p>
                    This is an independent, unofficial fandom software project.
                    It is not the official home of any franchise and is not
                    affiliated with, sponsored by, or endorsed by any studio,
                    creator, cast member, publisher, or rights holder.
                </p>
            </aside>
        </>
    );
}
